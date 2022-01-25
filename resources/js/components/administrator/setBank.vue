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
              Отправить на {{ howrows }}
              {{ plueral(howrows, ["запись", "записи", "записей"]) }} выбранный
              банк?
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
      <v-col cols="8">
        <div id="filter">
          <v-row>
            <!-- registration -->
            <v-col cols="4">
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
                    dense="true"
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
            <v-col cols="4">
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
                    dense="true"
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
            <v-col cols="4">
              <v-text-field
                label="Наименование:"
                id="firm"
                v-model="firm"
                hide-details="true"
                dense="true"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row class="align-center">
            <!-- fio -->
            <v-col cols="3">
              <v-text-field
                label="ФИО:"
                id="fio"
                v-model="fio"
                hide-details="true"
                dense="true"
              ></v-text-field>
            </v-col>

            <!-- inn -->
            <v-col cols="2">
              <v-text-field
                label="ИНН"
                id="inn"
                v-model.number="inn"
                hide-details="true"
                dense="true"
              ></v-text-field>
            </v-col>

            <!-- address -->
            <v-col cols="2">
              <v-text-field
                label="Адрес"
                id="address"
                v-model="address"
                hide-details="true"
                dense="true"
              ></v-text-field>
            </v-col>

            <!-- reg -->
            <v-col cols="2">
              <v-text-field
                label="Регион"
                id="region"
                v-model="region"
                hide-details="true"
                dense="true"
              ></v-text-field>
            </v-col>
            <!-- btn -->
            <v-col>
              <v-btn
                color="primary"
                elevation="2"
                outlined
                raised
                @click="getClients"
                >Фильтр <v-icon> mdi-table-large </v-icon></v-btn
              >
            </v-col>
          </v-row>
        </div>
      </v-col>
      <v-col cols="4">
        <div class="btn4">
          <v-autocomplete
            v-model="selectedBanks"
            :items="banks"
            outlined
            dense
            chips
            small-chips
            item-text="name"
            item-value="id"
            label="Назначить банк"
            @change="dialog = true"
            hide-details="true"
          ></v-autocomplete>

          <v-checkbox
            class="mt-2"
            color="red"
            v-model="checkBanks"
            label="С проверкой"
            hide-details="true"
          ></v-checkbox>

          <!-- hide bank -->

          <v-autocomplete
            outlined
            dense
            chips
            small-chips
            v-model="hidedBank"
            :items="banks"
            item-text="name"
            item-value="id"
            :menu-props="{ maxHeight: '400' }"
            label="Спрятать банк"
            multiple
            hide-details="true"
          ></v-autocomplete>

          <v-btn
            :disabled="!selected.length"
            outlined
            raised
            @click="dialogDelClients = true"
            height="40"
            >
            Удалить из базы
            </v-btn>
        </div>
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
                  <v-col cols="12">
                    <v-row class="justify-start align-end mb-5">
                      <span class="ml-10">Отобрать</span>
                      <span class="mx-4 hmrows">
                        <v-text-field
                          label="Сколько?"
                          @input="selectRow"
                          :max="filterClients.length"
                          v-model.number.lazy="hmrow"
                          hide-details="auto"
                        ></v-text-field>
                      </span>
                      <span> записей из {{ filterClients.length }}</span>
                      <v-spacer></v-spacer>
                    </v-row>
                  </v-col>
                  <v-spacer></v-spacer>
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
    dialogDelClients: false,
    loading: false,
    checkBanks: false,
    dialog: false,
    selected: [],
    userid: null,
    disableuser: 0,
    selectedBanks: [],
    banks: [],
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
      { text: "Банк:Воронка", value: "banksfunnels" },
    ],
    clients: [],
    banksfunnels: [],
    headers: [],
    message: "",
    snackbar: false,
    hmrow: "",
  }),
  mounted() {
    this.getBanks();
    // this.getUsers();
    this.getClientsWithoutBanks();
  },
  computed: {
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
    delClients() {
      if (this.selected.length) {
        let self = this;
        let send = {};
        send.client_ids = this.selected.map(function (i) {
          return i.id;
        });
        axios
          .post("/api/delClients", send)
          .then((res) => {
            self.selected = [];
            self.message = "Удалены выбранные строки: " + res.data;
            self.snackbar = true;
            self.this.getClientsWithoutBanks();
          })
          .catch((error) => console.log(error));
      }
    },
    selectRow() {
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
      axios
        .get("/api/getClientsWithoutBanks")
        .then((res) => {
          self.clients = Object.entries(res.data).map((e) => e[1]);
        })
        .catch((error) => console.log(error));
    },
    setBankForClients(bank_id) {
      let self = this;
      let send = {};
      send.bank_id = bank_id;
      send.checkBanks = self.checkBanks;
      self.loading = true;
      if (this.selected.length > 0) {
        send.clients = this.selected.map((i) => i.id);
      } else {
        send.clients = this.filterClients.map((i) => i.id);
      }
      axios
        .post("/api/setBankForClients", send)
        .then((res) => {
          self.getClientsWithoutBanks();
          self.loading = false;
          self.selected = [];
          self.selectedBanks = 0;
          self.hmrow = "";

          self.message =
            "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
          self.snackbar = true;
        })
        .catch((error) => console.log(error));
    },
    clickrow() {},
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

    getClients() {
      const self = this;
      let send = {};
      const filter = document.getElementById("filter");
      const inputs = filter.querySelectorAll("input");
      inputs.forEach(function (el) {
        if (el.value != "") send[el.getAttribute("id")] = el.value;
      });
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
        })
        .catch((error) => console.log(error));
    },
  },
  components: {},
};
</script>

<style scoped>
.hmrows {
  width: 100px;
}
.btn4 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-gap: 15px;
}
@media (max-width: 1024px){
  .btn4 {
  grid-template-columns: 1fr;
}
}
</style>
