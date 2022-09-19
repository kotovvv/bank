<template>
  <div>
    <v-snackbar v-model="snackbar" top right :timeout="-1">
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
              Отправить {{ howrows }}
              {{ plueral(howrows, ["запись", "записи", "записей"]) }} на
              выбранный банк?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
                setBankForClients(selectedBanks);
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
    <v-dialog
      transition="dialog-top-transition"
      max-width="600"
      v-model="dialogDelClients"
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
              Удалить выбранные записи без возможности восстановления?
            </div>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              @click="
                delClients();
                dialogDelClients = false;
              "
              >Да</v-btn
            >
            <v-spacer></v-spacer>
            <v-btn text @click="dialogDelClients = false">Нет</v-btn>
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>
    <v-row>
      <v-col cols="12">
        <div id="filter" class="my-3">
          <v-row>
            <!-- registration -->
            <v-col>
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
                    prepend-icon="mdi-calendar"
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
                  <v-btn
                    text
                    color="primary"
                    @click="$refs.dialog.save(dateReg)"
                  >
                    Выбрать
                  </v-btn>
                </v-date-picker>
              </v-dialog>
            </v-col>

            <!-- download -->
            <v-col>
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

            <!-- banks -->
            <v-col>
              <v-select
                v-model="filterBanks"
                :items="banks"
                :dense="true"
                id="banks"
                item-text="name"
                item-value="id"
                label="Банк"
                multiple
                hide-details="true"
                :value="selected"
              >
              </v-select>
            </v-col>

            <!-- funnels -->
            <v-col>
              <v-select
                v-model="filterFunnels"
                :items="funnels"
                :dense="true"
                id="funnels"
                item-text="name"
                item-value="id"
                label="Статусы"
                multiple
                hide-details="true"
              >
              </v-select>
            </v-col>
          </v-row>
          <v-row class="align-center">
            <!-- firm -->
            <v-col>
              <v-text-field
                label="Наименование:"
                id="firm"
                v-model="firm"
                hide-details="true"
                :dense="true"
              ></v-text-field>
            </v-col>

            <!-- fio -->
            <v-col>
              <v-text-field
                label="ФИО:"
                id="fio"
                v-model="fio"
                hide-details="true"
                :dense="true"
              ></v-text-field>
            </v-col>

            <!-- inn -->
            <v-col>
              <v-text-field
                label="ИНН"
                id="inn"
                v-model.number="inn"
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
            <v-spacer></v-spacer>

            <!-- btn -->
            <v-btn
              class="mr-3"
              color="primary"
              elevation="2"
              outlined
              raised
              @click="getClients"
              >Фильтр <v-icon> mdi-table-large </v-icon></v-btn
            >
          </v-row>
        </div>
      </v-col>
    </v-row>
    <v-row>
        <v-col>
                            <v-btn v-if="checkedClientsVTB.length > 0" color="teal lighten-1">
                      <download-csv
                        :data="checkedClientsVTB"
                        delimiter=";"
                        :name="
                          'check_clients_VTB-' +
                          new Date().toLocaleDateString() +
                          '.csv'
                        "
                      >
                        Экспорт проверки
                      </download-csv>
                    </v-btn>
      </v-col>
    </v-row>
    <template>
      <v-row>
        <v-progress-linear
          :active="loading"
          :indeterminate="loading"
          color="deep-purple accent-4"
        ></v-progress-linear>
        <v-col cols="12" v-if="filterClients.length">
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
            >
              <template v-slot:top="{}">
                <v-row>
                  <v-col>
                    <v-row class="mb-5">
                      <span class="ml-7 mx-4 d-flex align-end"
                        >Отбор
                        <v-text-field
                          class="mx-2 talign-center nn"
                          label="Сколько?"
                          @input="selectRow"
                          :max="filterClients.length"
                          v-model.number.lazy="hmrow"
                          hide-details="auto"
                          color="#004D40"
                        ></v-text-field>
                        из {{ filterClients.length }}
                      </span>
                    </v-row>
                  </v-col>

                  <v-col>
                    <v-autocomplete
                      v-model="selectedBanks"
                      :items="banks"
                      outlined
                      dense
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
                        <v-list-item
                          v-on="on"
                          v-bind="attrs"
                          #default="{ active }"
                        >
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
                    </v-autocomplete>

                    <v-checkbox
                      class="mt-2"
                      color="red"
                      v-model="checkBanks"
                      label="С проверкой"
                      hide-details="true"
                    ></v-checkbox>
                  </v-col>

                  <!-- hide bank -->
                  <v-col>
                    <v-autocomplete
                      outlined
                      dense
                      v-model="hidedBank"
                      :items="banks"
                      item-text="name"
                      item-value="id"
                      :menu-props="{ maxHeight: '400' }"
                      label="Спрятать банк"
                      multiple
                      hide-details="true"
                    ></v-autocomplete>
                  </v-col>
                  <v-spacer></v-spacer>
                  <v-col class="d-flex align-right mr-2" style="grid-gap: 1rem">
                    <!-- del from base -->
                    <v-btn
                      :disabled="!selected.length"
                      outlined
                      raised
                      @click="dialogDelClients = true"
                      height="40"
                      color="red"
                    >
                      Удалить из базы
                    </v-btn>
                    <v-btn
                      :disabled="btn_act"
                      outlined_
                      raised
                      @click="dialog = true"
                      height="40"
                      color="success"
                    >
                      Назначить
                    </v-btn>

                    <v-btn v-if="$attrs.user.role_id == 1">
                      <download-csv
                        :data="filterClients"
                        delimiter=";"
                        :name="
                          'osn-' + new Date().toLocaleDateString() + '.csv'
                        "
                      >
                        Экспорт
                      </download-csv>
                    </v-btn>
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
import JsonCSV from "vue-json-csv";
import _ from "lodash";
export default {
  data: () => ({
    dialogDelClients: false,
    loading: false,
    checkBanks: false,
    dialog: false,
    selected: [],
    userid: null,
    disableuser: 0,
    filterBanks: [],
    selectedBanks: [],
    banks: [],
    filterFunnels: [],
    hidedBank: [],
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
      //   { text: "Банк:Воронка", value: "banksfunnels" },
    ],
    clients: [],
    banksfunnels: [],
    funnels: [],
    filterfunnels: [],
    headers: [],
    message: "",
    snackbar: false,
    hmrow: "",
    checkedClientsVTB: [],
  }),
  mounted() {
    this.getBanks();
    // this.getUsers();
    this.getFunnels();
    this.getClientsWithoutBanks();
  },
  computed: {
    btn_act() {
      if (this.hmrow > 0 && this.selectedBanks > 0) return false;
      return true;
    },
    filterClients() {
      let re = "";
      re = this.hidedBank.map((i) => '"' + i + ":").join("|");
      re = "(" + re + ")";
      let reg = new RegExp(re);
      return this.clients.filter((i) => {
        return !this.hidedBank.length || !reg.test(i.banksfunnels);
      });
    },
    howrows: function () {
      return this.selected.length
        ? this.selected.length
        : this.filterClients.length;
    },
  },
  watch: {},
  methods: {
    getFunnels() {
      let self = this;
      axios
        .get("/api/funnels")
        .then((res) => {
          self.funnels = res.data;
        })
        .catch((error) => console.log(error));
    },
    delClients() {
      if (this.selected.length) {
        let self = this;
        let send = {};
        self.clients = [];
        self.loading = true;
        send.client_ids = this.selected.map(function (i) {
          return i.id;
        });
        axios
          .post("/api/delClients", send)
          .then((res) => {
            self.selected = [];
            self.loading = false;
            self.message = "Удалены выбранные строки: " + res.data;
            self.snackbar = true;
            self.getClientsWithoutBanks();
          })
          .catch((error) => console.log(error));
      }
    },
    selectRow() {
      this.checkedClientsVTB = [];
      this.selected = this.filterClients.slice(0, this.hmrow);
    },
    plueral(number, words) {
      return words[
        number % 100 > 4 && number % 100 < 20
          ? 2
          : [2, 0, 1, 1, 1, 2][number % 10 < 5 ? Math.abs(number) % 10 : 5]
      ];
    },
    getClientsWithoutBanks() {
      let self = this;
      self.clients = [];
      self.loading = true;
      axios
        .get("/api/getClientsWithoutBanks")
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);
          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
    setBankForClients(bank_id) {
      let self = this;
      self.message = ''
      let send = {};
      let clients = {};
      send.bank_id = bank_id;
      send.checkBanks = self.checkBanks;
      send.user_id = self.$attrs.user.id;
      self.loading = true;
      if (this.selected.length > 0) {
        clients = this.selected;
      } else {
        clients = this.filterClients;
      }
      send.clients = clients.map((i) => i.id);

      //   if need check client in bank
      if (self.checkBanks) {
        let alldone = {};
        alldone.all = send.clients.length;
        alldone.done = 0;
        self.snackbar = true;

        self.checkClient(send, alldone, clients);
      } else {
        axios
          .post("/api/setBankForClients", send, { timeout: 60 * 15 * 1000 })
          .then((res) => {
            self.getClientsWithoutBanks();
            self.loading = false;
            self.selected = [];
            self.selectedBanks = 0;
            self.hmrow = "";

            self.message =
              "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
            console.log(res.data.done, res.data.all);
            self.snackbar = true;
          })
          .catch((error) => console.log(error));
      }
    },
    clickrow() {},

    async checkClient(send, alldone, clients) {
      const self = this;
      self.checkedClientsVTB = [];
      const clients_ids = send.clients;
      if (send.bank_id == 4) {
        axios
          .post("/api/chekLidsVTB", send)
          .then((res) => {

            const rclients = res.data.rclients;
            if (res.data.successful) {
                // merge clients and ansver bank
              clients.map(
                ({
                  inn,
                  fullName,
                  phoneNumber,
                  organizationName,
                  address,
                  region,
                  registration,
                  initiator,
                  date_added,
                }) => {
                  self.checkedClientsVTB.push({
                    inn,
                    fullName,
                    phoneNumber,
                    organizationName,
                    address,
                    region,
                    registration,
                    initiator,
                    date_added,
                    responseCode: _.find(rclients, { inn: inn })
                      .responseCodeDescription,
                  });
                }
              );
              self.message =
                "Разрешено: " +
                res.data.allow +
                "<br>Отменено: " +
                res.data.desallow;
            } else {
              self.message = "Ошибка: " + res.data.message;
            }
            self.afterCheckClient();
          })
          .catch((error) => console.log(error));

      } else {
        // portion send clients in bank
        (async () => {
          for (let step = 0, show = 100; alldone.all - step * show; step++) {
            send.clients = clients_ids.slice(step * show, show + step * show);
            // Currently using await before axios call
            if (alldone.all < step * show) break;
            await axios
              .post("/api/setBankForClients", send)
              .then((res) => {
                alldone.done += parseInt(res.data.done);
                // console.log(alldone.done);
                self.message =
                  "Записей: " + alldone.all + "<br>Изменено: " + alldone.done;
                  self.afterCheckClient();
              })
              .catch((error) => console.log(error));
          }

        })();
      }

    },
    afterCheckClient() {
      const self = this;
      self.getClientsWithoutBanks();
      self.loading = false;
      self.selected = [];
      self.selectedBanks = 0;
      self.hmrow = "";
    },
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.banks = res.data.map(({ id, name, abbr, color }) => ({
            id,
            name,
            abbr,
            color,
          }));
        })
        .catch((error) => console.log(error));
    },

    getClients() {
      const self = this;
      let send = {};
      const filter = document.getElementById("filter");
      const inputs = filter.querySelectorAll("input");
      inputs.forEach(function (el) {
        if (el.value != "" && el.getAttribute("id"))
          send[el.getAttribute("id")] = el.value;
      });
      if (this.filterBanks.length) {
        send.banks = this.filterBanks;
      }
      if (this.filterFunnels.length) {
        send.funnels = this.filterFunnels;
      }
      self.loading = true;
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
              banksfunnels,
            })
          );
          self.loading = false;
        })
        .catch((error) => console.log(error));
    },
  },
  components: {
    downloadCsv: JsonCSV,
  },
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
