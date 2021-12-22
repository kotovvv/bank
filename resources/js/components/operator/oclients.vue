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
      v-model="dialog"
      persistent
      fullscreen
      hide-overlay
    >
      <!-- max-width="800" -->
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
              <v-col cols="4">
                <ul style="color: #000">
                  <li><b>ФИО: </b>{{ selected.fullName }}</li>
                  <li><b>Тел: </b>{{ selected.phoneNumber }}</li>
                  <li><b>Наименование: </b>{{ selected.organizationName }}</li>
                  <li><b>ИНН: </b>{{ selected.inn }}</li>
                  <li><b>Адрес: </b>{{ selected.address }}</li>
                  <li><b>Регион: </b>{{ selected.region }}</li>
                  <li><b>Регистрация: </b>{{ selected.registration }}</li>
                </ul>
              </v-col>
              <v-col cols="8">
                <v-progress-linear
                  color="deep-purple accent-4"
                  indeterminate
                  rounded
                  height="6"
                  v-if="wait"
                ></v-progress-linear>
                <v-btn
                  depressed
                  color="primary"
                  @click="requestBank"
                  v-if="reqBtn"
                  >Запрос на звонок</v-btn
                >

                <div
                  id="answer_bank"
                  v-if="answer_bank"
                  class="pa-1"
                  :class="{
                    'green white--text': responseBank.status == 'allowed',
                    'pink white--text': responseBank.status != 'allowed',
                  }"
                >
                  {{ answer_bank }}
                </div>

                <!-- answer client -->
                <div class="mt-5" v-show="step == 2">
                  <h2>Ответ банку</h2>
                  <div class="my-5">
                    <v-btn
                      depressed
                      color="green"
                      @click="
                        step = 3;
                        updateStatus('agree');
                      "
                      >Клиент согласен на продукт</v-btn
                    >
                  </div>
                  <div class="my-5">
                    <v-btn
                      depressed
                      color="pink accent-1"
                      @click="
                        group_status = 4;

                        updateStatus('disagree');
                      "
                      >Клиент не согласен на продукт</v-btn
                    >
                  </div>
                  <div class="my-5">
                    <v-btn
                      depressed
                      color="orange darken-4"
                      @click="
                        step = 3;
                        updateStatus('call_back');
                      "
                      >Клиент просил перезвонить</v-btn
                    >
                  </div>
                </div>
                <div
                  class="mt-5"
                  v-show="group_status != 0"
                  :key="group_status"
                >
                  <h2>Выбор статуса</h2>
                  <v-container class="px-0" fluid>
                    <v-radio-group v-model="selectedFunnel">
                      <v-radio
                        v-for="funnel in group_status_filter()"
                        :key="funnel.id"
                        color="funnel.color"
                        :label="`${funnel.name}`"
                        :value="funnel.id"
                        @click="dialogf = true"
                      ></v-radio>
                    </v-radio-group>
                  </v-container>
                </div>
                <!-- form step 3 -->
                <v-container v-show="step == 3">
                  <h2>Форма заявки</h2>
                  <v-row>
                    <v-col cols="6">
                      <v-autocomplete
                        v-model="model_region"
                        :items="show_regions"
                        :loading="isLoading"
                        :search-input.sync="search_region"
                        chips
                        clearable
                        hide-details
                        hide-selected
                        item-text="name"
                        item-value="id"
                        label="Регион"
                      >
                        <!-- solo -->
                        <template v-slot:no-data>
                          <v-list-item>
                            <v-list-item-title>
                              Введите первые символы
                              <strong>региона</strong>
                            </v-list-item-title>
                          </v-list-item>
                        </template>
                        <template
                          v-slot:selection="{ attr, on, item, selected_region }"
                        >
                          <v-chip
                            v-bind="attr"
                            :input-value="selected_region"
                            color="blue-grey"
                            class="white--text"
                            v-on="on"
                          >
                            <span v-text="item.name"></span>
                          </v-chip>
                        </template>
                        <template v-slot:item="{ item }">
                          <v-list-item-content>
                            <v-list-item-title
                              v-text="item.name"
                            ></v-list-item-title>
                          </v-list-item-content>
                        </template>
                      </v-autocomplete>
                    </v-col>
                    <!-- city -->
                    <v-col cols="6">
                      <v-autocomplete
                        v-model="model_city"
                        :items="show_cities"
                        :loading="isLoading"
                        :search-input.sync="search_city"
                        chips
                        clearable
                        hide-details
                        hide-selected
                        item-text="name"
                        item-value="id"
                        label="Город"
                      >
                        <!-- solo -->
                        <template v-slot:no-data>
                          <v-list-item>
                            <v-list-item-title>
                              Введите первые символы
                              <strong>города</strong>
                            </v-list-item-title>
                          </v-list-item>
                        </template>
                        <template
                          v-slot:selection="{ attr, on, item, selected_city }"
                        >
                          <v-chip
                            v-bind="attr"
                            :input-value="selected_city"
                            color="blue-grey"
                            class="white--text"
                            v-on="on"
                          >
                            <span v-text="item.name"></span>
                          </v-chip>
                        </template>
                        <template v-slot:item="{ item }">
                          <v-list-item-content>
                            <v-list-item-title
                              v-text="item.name"
                            ></v-list-item-title>
                          </v-list-item-content>
                        </template>
                      </v-autocomplete>
                    </v-col>
                    <v-col cols="12" v-show="departments.length">
                      <!-- :hint="`${department.name}, ${department.address}`" -->
                      <v-select
                        v-model="department"
                        :items="departments"
                        item-text="name"
                        item-value="id"
                        label="Отделение"
                        persistent-hint
                        return-object
                        single-line
                      >
                      </v-select>
                    </v-col>
                    <v-col cols="4">
                      <v-btn text @click="rr = 0">
                        <v-icon> mdi-close </v-icon>
                        <h3>Расчётный счёт</h3>
                      </v-btn>

                      <v-radio-group v-model="rr">
                        <v-radio
                          label="Пакет услуг Легкий старт"
                          :value="18"
                        ></v-radio>
                        <v-radio
                          label="Пакет услуг Набирая обороты"
                          :value="49"
                        ></v-radio>
                        <v-radio
                          label="Пакет услуг Полным ходом"
                          :value="50"
                        ></v-radio>
                      </v-radio-group>
                    </v-col>
                    <v-col cols="4">
                      <v-btn text @click="pp = 0">
                        <v-icon> mdi-close </v-icon>
                        <h3>Приём платежей</h3>
                      </v-btn>

                      <v-radio-group v-model="pp">
                        <v-radio
                          label="Торговый эквайринг"
                          :value="31"
                        ></v-radio>
                        <v-radio label="Смарт-терминал" :value="42"></v-radio>
                      </v-radio-group>
                    </v-col>
                    <v-col cols="4">
                      <v-btn text @click="ss = 0">
                        <v-icon> mdi-close </v-icon>
                        <h3>Сервисы</h3>
                      </v-btn>

                      <v-radio-group v-model="ss">
                        <v-radio label="Сберздоровье" :value="46"></v-radio>
                      </v-radio-group>
                    </v-col>
                  </v-row>
                  <v-btn depressed color="primary" @click="sendZ"
                    >Отправить заявку</v-btn
                  >
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
import _ from "lodash";
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
    set_status: false,
    group_status: 0,
    step: 1,
    responseBank: {},
    wait: false,
    reqBtn: true,
    answer_bank: "",
    isLoading: false,
    regions: [],
    model_region: null,
    search_region: null,
    show_regions: [],
    cities: [],
    model_city: null,
    search_city: null,
    show_cities: [],
    department: {},
    departments: [],
    rr: 0,
    pp: 0,
    ss: 0,
  }),
  mounted() {
    this.getBanks();
    this.getFunnels();
    this.getUserClients();
  },
  watch: {
    model_city(val) {
      const self = this;
      if (val != null) {
        let send = {};
        send.bank_id = this.selectedBank;
        send.region_id = this.model_region;
        send.city_id = this.model_city;
        // Lazily load input items
        axios
          .post("/api/getDepartments", send)
          .then((res) => {
            self.departments = res.data;
            self.departments.map((i) => {
              i.name = i.name + " | " + i.address;
            });
          })
          .catch((error) => console.log(error));
      }
    },
    search_region(val) {
      if (this.regions.length == 0) {
        if (this.isLoading) return;

        this.isLoading = true;

        // Lazily load input items
        fetch(window.location.href + "api/getRegions")
          .then((res) => res.json())
          .then((res) => {
            this.regions = res;
          })
          .catch((err) => {
            console.log(err);
          })
          .finally(() => (this.isLoading = false));
      } else {
        val && this.queryRegion(val);
      }
    },
    search_city(val) {
      const self = this;
      //  if (this.cities.length == 0) {
      if (this.isLoading) return;

      this.isLoading = true;

      let send = {};
      send.bank_id = this.selectedBank;
      send.region_id = this.model_region;
      send.query = val;
      // Lazily load input items
      axios
        .post("/api/getCities", send)
        .then((res) => {
          self.cities = res.data.entries;
          self.isLoading = false;
          val && self.queryCity(val);
        })
        .catch((error) => console.log(error));
      // } else {
      // }
    },
  },
  computed: {},
  methods: {
    sendZ() {},

    queryRegion(search) {
      this.cities = [];
      this.model_city = null;
      this.search_city = null;
      this.show_cities = [];
      this.show_regions = this.regions.filter((e) => {
        return e.name.toLowerCase().indexOf(search.toLowerCase()) > -1
          ? e
          : false;
      });
    },
    queryCity(search) {
      if (this.cities.length > 0) {
        this.show_cities = this.cities.filter((e) => {
          return e.name.toLowerCase().indexOf(search.toLowerCase()) > -1
            ? e
            : false;
        });
        return false;
      }
    },
    group_status_filter() {
      return _.filter(this.funnels, { group: this.group_status });
    },
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
          self.reqBtn = true;
          self.step = 1;
          self.group_status = 0;
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
      this.reqBtn = false;
      this.wait = true;
      const self = this;
      const item = this.selected;
      let send = {};
      send.data = { phone: "+" + item.phoneNumber, inn: item.inn };
      send.bank_id = this.selectedBank;
      send.action = "call_requests";

      axios
        .post("/api/canTel", send)
        .then((res) => {
          self.wait = false;
          self.reqBtn = true;
          self.group_status = 0;
          if (res.data.status == "forbidden") self.group_status = 1;
          if (res.data.status == "blocked") self.group_status = 2;
          if (res.status == 200 && res.data.status == "allowed") {
            self.reqBtn = false;
            self.answer_bank = res.data.status_translate;
            self.responseBank = res.data;
            self.step = 2;
          }
          console.log(res);
        })
        .catch((error) => {
          self.wait = false;
          self.reqBtn = true;
          self.answer_bank = "";
          console.error(error.message);
        });
    },
    updateStatus(status) {
      const self = this;
      this.wait = true;
      let send = {};
      send.data = { call_status: status };
      send.bank_id = this.selectedBank;
      send.client_id = self.responseBank.id;
      send.action = "updateStatus";
      axios
        .post("/api/updateStatus", send)
        .then((res) => {
          self.wait = false;
          console.log(res);
        })
        .catch((error) => {
          self.wait = false;
          self.answer_bank = "";
          console.error(error.message);
        });
    },
  },
  components: {},
};
</script>

<style>
</style>
