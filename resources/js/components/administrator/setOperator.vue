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
        <v-row id="filter" class="my-5">
          <!-- bank -->
          <v-col cols="2">
            <v-select
              v-model="selectedBank"
              :items="banks"
              outlined
              :dense="true"
              item-text="name"
              item-value="id"
              label="Банк"
              hide-details="true"
            >
            <template v-slot:selection="{ item }">
              <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              >{{ item.name }}
            </template>
              <template v-slot:item="{ active, item, attrs, on }">
                <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                  <v-list-item-content>
                    <v-list-item-title>
                      <v-row no-gutters align="center">
                        <i
                :style="{
                  background: item.color,
                  outline: '1px solid grey',
                }"
                class="sel_stat mr-4"
              ></i
              ><span>{{ item.name }}</span>
                        <v-spacer></v-spacer>
                        <v-chip
                          text-color="white"
                          class="indigo darken-4"
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
                  hide-details="true"
                  :dense="true"
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
          <!-- download -->
          <v-col cols="2">
            <v-dialog
              ref="dialog2"
              v-model="modal2"
              :return-value.sync="dateAdd"
              persistent
              width="330px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model.lazy="dateAdd"
                  label="Загрузка (период)"
                  prepend-icon="mdi-calendar"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  id="dateadd"
                  hide-details="true"
                  :dense="true"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model.lazy="dateAdd"
                scrollable
                range
                locale="ru-ru"
              >
                <v-spacer></v-spacer>
                <v-btn text color="primary" @click="modal2 = false">
                  Отмена
                </v-btn>
                <v-btn
                  text
                  color="primary"
                  @click="
                    dateAdd = [];
                    $refs.dialog2.save(dateAdd);
                    modal2 = false;
                  "
                >
                  Очистить
                </v-btn>
                <v-btn
                  text
                  color="primary"
                  @click="$refs.dialog2.save(dateAdd)"
                >
                  Выбрать
                </v-btn>
              </v-date-picker>
            </v-dialog>
          </v-col>
          <!-- firm -->
          <v-col cols="1">
            <v-text-field
              label="Наименование:"
              id="firm"
              v-model="firm"
              hide-details="true"
              :dense="true"
            ></v-text-field>
          </v-col>
          <!-- address -->
          <v-col>
            <v-text-field
              label="Адрес"
              id="address"
              v-model="address"
              hide-details="true"
              :dense="true"
            ></v-text-field>
          </v-col>

          <!-- reg -->
          <v-col>
            <v-text-field
              label="Регион"
              id="region"
              v-model="region"
              hide-details="true"
              :dense="true"
            ></v-text-field>
          </v-col>

          <!-- btn -->
          <v-col>
            <v-btn
              color="primary"
              elevation="2"
              outlined
              raised
              @click="
                getClients(selectedBank);
                this.firstRequest = false;
              "
              >Фильтр <v-icon> mdi-table-large </v-icon></v-btn
            >
          </v-col>
          <v-spacer></v-spacer>

          <v-col v-if="selected.length && selectedBank">
            <v-btn
              color="error"
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
        <v-progress-linear
          :active="loading"
          :indeterminate="loading"
          color="deep-purple accent-4"
        ></v-progress-linear>
        <v-col cols="12" v-if="clients.length">
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
              <template v-if="clients.length" v-slot:top="{}">
                <v-row class="mb-3">
                  <v-col cols="8">
                    <div class="d-flex ml-5 align-end">
                      <v-text-field
                        label="Сколько?"
                        v-model.number.lazy="hmrow"
                        @input="selectRow"
                        :max="clients.length"
                        class="talign-center"
                        color="#004D40"
                        hide-details="true"
                        :dense="true"
                      ></v-text-field>
                      <v-card-text class="pa-0"
                        >из:
                        <span class="text-h5">{{ filterClients.length }}</span>
                        и назначить оператору</v-card-text
                      >
                    </div>
                  </v-col>
                  <v-col cols="4">
                    <v-select
                      class="mr-5"
                      @change="dialogset = true"
                      v-model="userid"
                      :items="users"
                      outlined
                      :dense="true"
                      item-text="fio"
                      item-value="id"
                      label="Операторы"
                      hide-details="true"
                    >
                    </v-select>
                  </v-col>
                </v-row>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
        <!-- <v-col cols="3">
          <div class="row">
            <v-card class="pa-5 w-100">

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
                    </v-row>
                  </v-radio-group>
                </v-list>
              </v-card-text>
            </v-card>
          </div>
        </v-col> -->
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
            <v-btn text @click="dialogo = false">Нет</v-btn>
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
                selectedBanks = [];
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
    dialog2: false,
    selected: [],
    userid: null,
    disableuser: 0,
    selectedBanks: [],
    banks: [],
    users: [],
    tab: null,
    dateReg: [],
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
      //   { text: "Оператор", value: "operator" },
      //   { text: "Оператор ID", value: "user_id" },
      //   { text: "Банк:Воронка", value: "banksfunnels" },
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
    loading: false,
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
      self.loading = true;
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
          self.loading = false;
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
      self.loading = true;
      self.disableuser = id;
      axios
        .get("/api/getUserClients/" + id)
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);
          //   self.clients.map(function (e) {
          //     e.operator = self.users.find((u) => u.id == e.user_id).fio;
          //   });
          self.howmanybank();
          self.selectedBanks = [];
          self.loading = false;
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
      self.loading = true;
      if (self.selectedBank) send.bank_id = self.selectedBank;
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
          self.disableuser = 0;
          self.selected = [];
          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.banks = res.data.map(({ id, name, abbr, hm,color }) => ({
            id,
            name,
            abbr,
            hm,
            color
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
          if(self.$attrs.user.role_id == 2){
              self.users = self.users.filter(u=>u.group_id == self.$attrs.user.group_id && u.role_id == 3 )
          }
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
      if (
        self.banks.filter((i) => {
          return i.hm > 0;
        })
      ) {
        self.banks = self.banks.filter((i) => {
          return i.hm > 0;
        });
        // self.selectedBank = self.banks[0].id;
      }
    },
    getClients(empty = false) {
      const self = this;
      let send = {};
      self.loading = true;
      if (empty) {
        send.banksfunnelsNotEmpty = 1;
      } else {
        const filter = document.getElementById("filter");
        const inputs = filter.querySelectorAll("input");
        inputs.forEach(function (el) {
          if (el.value != "" && el.getAttribute("id"))
            send[el.getAttribute("id")] = el.value;
        });
        // if (this.selectedBank) send.bank_id = this.selectedBank;
        if (this.selectedBank && this.selectedFunnel) {
          send.bankfunnels = this.selectedBank + ":" + this.selectedFunnel;
        } else if (this.selectedBank) {
          send.bankfunnels = this.selectedBank + ":" + 0;
        }
      }
      if (this.dateAdd.length) {
        send.dateadd = this.dateAdd;
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
          if (empty) {
            self.howmanybank();
          }
          self.loading = false;
        })
        .catch((error) => {
          self.loading = false;
          console.log(error);
        });
    },
  },
  components: {},
};
</script>

<style>
.talign-center input {
  text-align: center;
}
.sel_stat {
  border-radius: 30px;
  height: 20px;
  min-width: 20px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: inherit;
  outline-offset: 3px;
  color: #fff;
  margin-left: 3px;
  margin-right: 4px;
  padding: 0 3px;
}
</style>
