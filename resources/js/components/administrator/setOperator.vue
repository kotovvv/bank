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

    <v-row>
      <v-col cols="12">
        <v-row id="filter">
          <!-- bank -->
          <v-col cols="2">
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
          <!-- registration -->
          <v-col cols="2">
            <v-dialog
              ref="dialog"
              v-model="modal"
              :return-value.sync="dateReg"
              persistent
              width="330px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model.lazy="dateReg"
                  label="Регистрация (период)"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  id="datereg"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model.lazy="dateReg"
                scrollable
                range
                locale="ru-ru"
              >
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="modal = false">
                  Отмена
                </v-btn>
                <v-btn
                  text
                  color="primary"
                  @click="
                    dateReg = [];
                    $refs.dialog.save(dateReg);
                    modal = false;
                  "
                >
                  Очистить
                </v-btn>
                <v-btn text color="primary" @click="$refs.dialog.save(dateReg)">
                  Выбрать
                </v-btn>
              </v-date-picker>
            </v-dialog>
          </v-col>
          <!-- firm -->
          <v-col cols="2">
            <v-text-field
              label="Наименование:"
              id="firm"
              v-model="firm"
            ></v-text-field>
          </v-col>
          <!-- address -->
          <v-col cols="1">
            <v-text-field
              label="Адрес"
              id="address"
              v-model="address"
            ></v-text-field>
          </v-col>

          <!-- reg -->
          <v-col cols="1">
            <v-text-field
              label="Регион"
              id="region"
              v-model="region"
            ></v-text-field>
          </v-col>

          <!-- btn -->
          <v-col cols="1">
            <v-btn
              color="primary"
              elevation="2"
              outlined
              raised
              @click="
                getClients;
                this.firstRequest = false;
              "
              >Фильтр <v-icon> mdi-table-large </v-icon></v-btn
            >
          </v-col>
          <v-spacer></v-spacer>

          <v-col v-if="selected.length && selectedBank">
            <v-btn
              color="primary"
              elevation="2"
              outlined
              raised
              @click="dialog = true"
              >Открепить банк</v-btn
            >
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <template>
      <v-row>
        <v-spacer></v-spacer>
        <v-col cols="9" v-if="clients.length">
          <v-card>
            <!-- :search="search" -->
            <v-data-table
              v-model.lazy.trim="selected"
              :headers="import_headers"
              :single-select="false"
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
              <template v-slot:top="{}">
                <v-row>
                  <v-spacer></v-spacer>
                  <v-col cols="6"> </v-col>
                </v-row>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
        <v-col cols="3">
          <div class="row">
            <v-card class="pa-5 w-100">
              <template v-if="clients.length">
                <v-text-field
                  label="Введите число"
                  v-model.number.lazy="hmrow"
                  @input="selectRow"
                  :max="clients.length"
                  :messages="'Сколько записей пометить?'"
                ></v-text-field>
                <v-card-text
                  >из: <span class="text-h5">{{ filterClients.length }}</span> и
                  назначить оператору</v-card-text
                >
              </template>
              <v-card-text class="scroll-y">
                <v-list>
                  <v-radio-group
                    @change="dialogset = true"
                    ref="radiogroup"
                    v-model="userid"
                    v-bind="users"
                    id="usersradiogroup"
                  >
                    <v-row v-for="user in users" :key="user.id">
                      <v-radio
                        :label="user.fio"
                        :value="user.id"
                        :disabled="disableuser == user.id"
                      >
                      </v-radio>

                      <!-- :color="usercolor(user)" -->
                      <!-- <v-btn
                        class="ml-3"
                        small
                        @click="getUserClients(user.id)"
                        :value="user.hmlids"
                        :disabled="disableuser == user.id"
                        >{{ user.hmlids }}</v-btn
                      > -->
                    </v-row>
                  </v-radio-group>
                </v-list>
              </v-card-text>
            </v-card>
          </div>
        </v-col>
      </v-row>
    </template>
    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="dialogset"
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
              Точно назначить выбранных клиентов оператору?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
                changeUserOfClients();
                dialogset = false;
              "
              >Да</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn
              text
              @click="
                dialogset = false;
                hmrow = '';
                userid = 0;
                selected = [];
              "
              >Нет</v-btn
            >
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>
    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="dialogo"
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
              Открепить оператора от выбранных клиентов?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
              userid = 0;
                changeUserOfClients();
                dialogo = false;
              "
              >Да</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn
              text
              @click="
                dialogo = false;
              "
              >Нет</v-btn
            >
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>
    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="dialog"
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
              Точно открепить выбранный банк из {{ selected.length }}
              {{
                plueral(selected.length, [
                  "выделенной строки",
                  "выделенных строк",
                  "выделенных строк",
                ])
              }}
              ?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
                delBankFromClients(selectedBank);
                dialog = false;
              "
              >Да</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn
              text
              @click="
                dialog = false;
                selectedBanks = 0;
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
    selected: [],
    userid: null,
    disableuser: 0,
    selectedBanks: [],
    banks: [],
    users: [],
    tab: null,
    dateReg: [],
    dateAdd: [],
    firm: "",
    fio: "",
    inn: "",
    address: "",
    region: "",
    menu: false,
    modal: false,
    modal2: false,
    import_headers: [
      // { text: "", value: "id" },
      { text: "ИНН", value: "inn" },
      { text: "ФИО", value: "fullName" },
      { text: "Тел", value: "phoneNumber" },
      { text: "Наименование", value: "organizationName" },
      { text: "Адрес", value: "address" },
      { text: "Регион", value: "region" },
      { text: "Регистрация", value: "registration" },
      { text: "Загрузка", value: "date_added" },
      { text: "Оператор", value: "operator" },
      { text: "Оператор ID", value: "user_id" },
      { text: "Банк:Воронка", value: "banksfunnels" },
    ],
    clients: [],
    headers: [],
    funnels: [],
    selectedFunnel: 0,
    selectedBank: 0,
    message: "",
    snackbar: false,
    hmrow: "",
    dialog: false,
    dialogo: false,
    dialogset: false,
    dateReg: [],
    dateAdd: [],
    firm: "",
    address: "",
    region: "",
    firstRequest: true,
  }),
  mounted() {
    this.getBanks();
    this.getFunnels();
    this.getUsers();
    this.getClients(this.firstRequest);
  },
  watch: {},
  computed: {
    filterClients() {
      let regfirm = new RegExp("^" + this.firm);
      let regaddress = new RegExp(this.address);
      let regregion = new RegExp(this.region);
      return this.clients.filter((i) => {
        return (
          (!this.selectedBank ||
            new RegExp('"' + this.selectedBank + ":").test(i.banksfunnels)) &&
          (!this.firm || regfirm.test(i.organizationName)) &&
          (!this.address || regaddress.test(i.address)) &&
          (!this.region || regregion.test(i.region))
        );
      });
    },
    howrows: function () {
      return this.selected.length
        ? this.selected.length
        : this.filterClients.length;
    },
  },
  methods: {
    delBankFromClients(bank_id) {
      let self = this;
      let send = {};
      send.bank_id = bank_id;
      send.clients = this.selected.map((i) => {
        return i.id;
      });
      axios
        .post("/api/delBankFromClients", send)
        .then((res) => {
          if (self.disableuser) {
            self.getUserClients(self.disableuser);
          } else {
            self.getClients();
          }
          self.message =
            "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
          self.snackbar = true;
          self.selected = [];
        })
        .catch((error) => console.log(error));
    },
    plueral(number, words) {
      return words[
        number % 100 > 4 && number % 100 < 20
          ? 2
          : [2, 0, 1, 1, 1, 2][number % 10 < 5 ? Math.abs(number) % 10 : 5]
      ];
    },
    selectRow() {
      this.selected = this.filterClients.slice(0, this.hmrow);
    },
    getUserClients(id) {
      let self = this;
      self.disableuser = id;
      axios
        .get("/api/getUserClients/" + id)
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);
          self.clients.map(function (e) {
            e.operator = self.users.find((u) => u.id == e.user_id).fio;
          });
          self.howmanybank();
          self.selectedBanks = 0;
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
    clickrow() {},
    changeUserOfClients() {
      let self = this;
      let send = {};
      if (self.selectedBank != 0 ) send.bank_id = self.selectedBank
      send.user_id = this.userid;
      if (this.selected.length) {
        send.clients = this.selected.map((i) => i.id);
      } else {
        send.clients = this.filterClients.map((i) => i.id);
      }
      axios
        .post("/api/changeUserOfClients", send)
        .then((res) => {
          self.getUsers();
          self.getClients(self.firstRequest);
          self.userid = null;
          self.disableuser=0
          self.selected=[]
        })
        .catch((error) => console.log(error));
    },
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.banks = res.data.map(({ id, name, abbr, hm }) => ({
            id,
            name,
            abbr,
            hm,
          }));
        })
        .catch((error) => console.log(error));
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data;
          self.hmrow = "";
        })
        .catch((error) => console.log(error));
    },
    howmanybank() {
      let self = this;
      let a = {};
      self.clients.map(function (i) {
        let client = "";
        let el = {};
        client = i.banksfunnels.replace(/"/g, "");
        if (/,/.test(client)) {
          //many clients
          client.split(",").map((i) => {
            el = i.split(":");
            if (a[el[0]] === undefined) a[el[0]] = 0;
            a[el[0]] += 1;
          });
        } else {
          //one client
          el = client.split(":");
          if (a[el[0]] === undefined) a[el[0]] = 0;
          a[el[0]] += 1;
        }
      });
      self.banks = self.banks.map(function (i) {
        if (i.id > 0) i.hm = a[i.id];
        return i;
      });
    },
    getClients(empty = false) {
      const self = this;
      let send = {};
      if (empty) {
        send.banksfunnelsNotEmpty = 1;
      } else {
        const filter = document.getElementById("filter");
        const inputs = filter.querySelectorAll("input");
        inputs.forEach(function (el) {
          if (el.value != "" && el.getAttribute("id"))
            send[el.getAttribute("id")] = el.value;
        });
        if (this.selectedBank) send.bank_id = this.selectedBank;
        if (this.selectedFunnel) send.funnel_id = this.selectedFunnel;
      }
      send.user_id = 0;
      axios
        .post("/api/getClients", send)
        .then((res) => {
          self.clients = res.data.map(
            ({
              id,
              inn,
              fullName,
              phoneNumber,
              organizationName,
              address,
              region,
              registration,
              date_added,
              user_id,
              banksfunnels,
            }) => ({
              id,
              inn,
              fullName,
              phoneNumber,
              organizationName,
              address,
              region,
              registration,
              date_added,
              user_id,
              banksfunnels,
            })
          );
          self.howmanybank();
        })
        .catch((error) => console.log(error));
    },
  },
  components: {},
};
</script>

<style>
</style>
