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
      max-width="600"
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
                Выбор статуса
                <v-container class="px-0" fluid>
                  <v-radio-group v-model="selectedFunnel">
                    <v-radio
                      v-for="funnel in funnels"
                      :key="funnel.id"
                      color="funnel.color"
                      :label="`${funnel.name}`"
                      :value="funnel.id"
                      @click="setBankForClients"
                    ></v-radio>
                  </v-radio-group>
                </v-container>
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
            <v-autocomplete
              v-model="selectedBank"
              :items="banks"
              outlined
              dense
              chips
              small-chips
              item-text="name"
              item-value="id"
              label="Банк"
              @change="changeFilter"
            ></v-autocomplete>
          </v-col>
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
  </div>
</template>

<script>
import axios from "axios";

export default {
  data: () => ({
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
    selected: [],
    banks: [],
    selectedBank: 1,
    funnels: [],
    selectedFunnel: 0,
    message: "",
    snackbar: false,
    dialog: false,
  }),
  mounted() {
    this.getBanks();
    this.getFunnels();
    this.getUserClients();
  },
  watch: {},
  methods: {
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
          self.selected = [];
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
        .get("/api/getUserClients/" + id + "/" + bank_id+'/'+funnel_id)
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);

          self.changeFilter();
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
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.banks = res.data.map(({ id, name, abbr }) => ({
            id,
            name,
            abbr,
          }));
        })
        .catch((error) => console.log(error));
    },
  },
  components: {},
};
</script>

<style>
</style>
