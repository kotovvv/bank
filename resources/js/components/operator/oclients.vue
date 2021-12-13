<template>
  <div>
    <v-snackbar v-model="snackbar" top right>
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <v-dialog
      transition="dialog-top-transition"
      max-width="800"
      v-model="dialog"
      persistent
    >
      <template>
        <v-card>
          <v-toolbar color="primary" dark
            ><v-icon large color="darken-2"> mdi-account-card-details </v-icon>
            <v-spacer></v-spacer>
            <v-btn icon dark @click="dialog = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </v-toolbar>
          <v-card-text>
            <v-row class="pt-3">
              <v-col cols="8">
                <ul>
                  <li><b>ФИО: </b>{{ selected.fullName }}</li>
                  <li><b>Тел: </b>{{ selected.phoneNumber }}</li>
                  <li><b>Наименование: </b>{{ selected.organizationName }}</li>
                  <li><b>ИНН: </b>{{ selected.inn }}</li>
                  <li><b>Адрес: </b>{{ selected.address }}</li>
                  <li><b>Регион: </b>{{ selected.region }}</li>
                  <li><b>Регистрация: </b>{{ selected.registration }}</li>
                </ul>
              </v-col>
              <v-col cols="4">
                <v-progress-linear
                  color="deep-purple accent-4"
                  indeterminate
                  rounded
                  height="6"
                  v-if="wait"
                ></v-progress-linear>
                <v-btn depressed color="primary" @click="requestBank" v-if="reqBtn">Запрос на звонок</v-btn>
                <div id="answer_bank" v-if="answer_bank">{{answer_bank}}</div>
                <div v-if="can_call">
                  Выбор статуса
                  <v-container class="px-0" fluid>
                    <v-radio-group v-model="selectedFunnel">
                      <v-radio
                        v-for="funnel in funnels"
                        :key="funnel.id"
                        color="funnel.color"
                        :label="`${funnel.name}`"
                        :value="funnel.id"
                        @click="dialogf = true"
                      ></v-radio>
                    </v-radio-group>
                  </v-container>
                </div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </template>
    </v-dialog>
    <v-row>
      <v-col cols="12">
        <v-row id="filter">
          <!-- bank -->
          <v-col cols="3">
            <v-select
              v-model="selectedBank"
              :items="banks"
              outlined
              dense
              chips
              small-chips
              item-text="name"
              item-value="id"
              label="Банк"
              hide-details="true"
              @change="changeFilter"
            >
              <template v-slot:item="{ active, item, attrs, on }">
                <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                  <v-list-item-content>
                    <v-list-item-title>
                      <v-row no-gutters align="center">
                        <span>{{ item.name }}</span>
                        <v-spacer></v-spacer>
                        <v-chip
                          text-color="white"
                          class="indigo darken-4"
                          small
                          v-if="item.hm > 0"
                          >{{ item.hm }}</v-chip
                        >
                      </v-row>
                    </v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
            </v-select>
          </v-col>
          <v-col
            >Всего: <span class="text-h5">{{ all }}</span></v-col
          >
        </v-row>
      </v-col>
    </v-row>
    <template>
      <v-row>
        <v-col cols="12">
          <v-card>
            <!-- :search="search" -->
            <!-- v-model.lazy.trim="selected" -->
            <v-data-table
              :headers="import_headers"
              :single-select="true"
              item-key="id"
              show-select
              @click:row="clickrow"
              :items="filterClients"
              ref="datatable"
              :footer-props="{
                'items-per-page-options': [50, 10, 100, 250, 500, -1],
                'items-per-page-text': 'Показать',
              }"
            >
              <template
                v-slot:top="{ pagination, options, updateOptions }"
                :footer-props="{
                  'items-per-page-options': [50, 10, 100, 250, 500, -1],
                  'items-per-page-text': 'Показать',
                }"
              >
                <v-row>
                  <v-spacer></v-spacer>
                  <v-col cols="6">
                    <v-data-footer
                      :pagination="pagination"
                      :options="options"
                      @update:options="updateOptions"
                      :items-per-page-options="[50, 10, 100, 250, 500, -1]"
                      :items-per-page-text="'Показать'"
                    />
                  </v-col>
                </v-row>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </template>
    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="dialogf"
    >
      <template>
        <v-card>
          <v-toolbar color="primary" dark
            ><v-icon large color="darken-2">
              mdi-alert-outline
            </v-icon></v-toolbar
          >

          <v-card-text>
            <div class="text-h4 pa-12">
              Установить выбранный статус для
              {{ selected != {} ? selected.fullName : "" }}?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
                dialogf = false;
                dialog = false;
                setBankForClients();
                selectedFunnel = 0;
              "
              >Да</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn
              text
              @click="
                dialogf = false;
                selectedFunnel = 0;
              "
              >Нет</v-btn
            >
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data: () => ({
    dialogf: false,
    all: "",
    done: "",
    import_headers: [
      // { text: "", value: "id" },
      { text: "ИНН", value: "inn" },
      { text: "ФИО", value: "fullName" },
      { text: "Тел", value: "phoneNumber" },
      { text: "Наименование", value: "organizationName" },
      { text: "Адрес", value: "address" },
      { text: "Регион", value: "region" },
      { text: "Регистрация", value: "registration" },
    ],
    filterClients: [],
    clients: [],
    selected: {},
    banks: [],
    selectedBank: 1,
    funnels: [],
    selectedFunnel: 0,
    message: "",
    snackbar: false,
    dialog: false,
    can_call: false,
statuses_ansver:{
  allowed:'Звонок разрешен',
  blocked:'Заблокирован',
  forbidden:'Не звонить',
},
responseBank:{},
wait:false,
reqBtn:true,
answer_bank:'',
  }),
  mounted() {
    this.getBanks();
    this.getFunnels();
    this.getUserClients();
  },
  watch: {},
  computed: {},
  methods: {
    howmanybank() {
      let self = this;
      let a = {};
      self.clients.map(function (i) {
        let bankfun = "";
        let el = {};
        bankfun = i.banksfunnels.replace(/"/g, "");
        if (/,/.test(bankfun)) {
          //many banks
          bankfun.split(",").map((i) => {
            el = i.split(":");
            if (a[el[0]] === undefined) a[el[0]] = 0;
            if (el[1] == 0) a[el[0]] += 1;
          });
        } else {
          //one bank
          el = bankfun.split(":");
          if (a[el[0]] === undefined) a[el[0]] = 0;
          if (el[1] == 0) a[el[0]] += 1;
        }
      });
      self.banks = self.banks.map(function (i) {
        if (i.id > 0) i.hm = a[i.id];
        return i;
      });
    },
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.banks = res.data;
        })
        .catch((error) => console.log(error));
    },
    setBankForClients() {
      let self = this;
      let send = {};
      send.bank_id = self.selectedBank;
      send.user_id = self.$attrs.user.id;
      send.funnel = self.selectedFunnel;
      send.clients = [self.selected.id];

      axios
        .post("/api/setBankForClients", send)
        .then((res) => {
          self.getUserClients();
          self.selected = {};
          self.dialog = false;
          self.message =
            "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
          self.snackbar = true;
        })
        .catch((error) => console.log(error));
    },
    changeFilter() {
      const self = this;
      let bank_id = 0;
      if (self.selectedBank) {
        bank_id = self.selectedBank;
        self.filterClients = self.clients.filter(
          (i) => i.banksfunnels.indexOf('"' + bank_id + ":") >= 0
        );
      } else {
        self.filterClients = self.clients;
      }
    },
    plueral(number, words) {
      return words[
        number % 100 > 4 && number % 100 < 20
          ? 2
          : [2, 0, 1, 1, 1, 2][number % 10 < 5 ? Math.abs(number) % 10 : 5]
      ];
    },

    getUserClients() {
      const self = this;
      const id = self.$attrs.user.id;
      let bank_id = 0;
      let funnel_id = 0;
      //   if (self.selectedBank) bank_id = self.selectedBank;
      axios
        .get("/api/getUserClients/" + id + "/" + bank_id + "/" + funnel_id)
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);

          self.changeFilter();
          self.howmanybank();
          self.all = self.clients.length;
        })
        .catch((error) => console.log(error));
    },
    getFunnels() {
      let self = this;
      axios
        .get("/api/funnels")
        .then((res) => {
          self.funnels = res.data;
        })
        .catch((error) => console.log(error));
    },
    clickrow(i, row) {
      row.select(true);
      this.selected = row.item;
      this.dialog = true;
    },
    requestBank() {
      this.reqBtn = false
      this.wait = true
      const self = this
      const item = this.selected;
      let send = {};
      send = { data: { phone: "+" + item.phoneNumber, inn: item.inn } };
      const bank = this.banks.find((i) => i.id ==this.selectedBank);
      if (bank.url == '') return

      axios({
        method: "post",
        data: send,
        url: bank.url + "/api/v1/call_easy/call_requests",
        headers: {
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Headers': '*',
          'Content-Type': 'application/json',
          'Authorization': "Token token="+bank.token,
        },
      })
        .then((response) => {
          self.wait = false
          self.reqBtn = true
          self.responseBank = response.data.status;

          console.log(self.response);
        })
        .catch((error) => {
          self.wait = false
          self.reqBtn = true
          console.error(error.message);
        });
    },
    updateStatus(){
// PATCH api/v1/call_easy/call_requests/:id
    },
  },
  components: {},
};
</script>

<style>
</style>
