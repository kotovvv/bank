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
      <v-col cols="9">
        <v-row id="filter">
          <!-- registration -->
          <v-col cols="2">
            <v-dialog
              ref="dialog"
              v-model="modal"
              :return-value.sync="dateReg"
              persistent
              width="290px"
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
              width="290px"
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
          <v-col cols="2">
            <v-text-field
              label="Наименование:"
              id="firm"
              v-model="firm"
            ></v-text-field>
          </v-col>

          <!-- fio -->
          <v-col cols="1">
            <v-text-field label="ФИО:" id="fio" v-model="fio"></v-text-field>
          </v-col>

          <!-- inn -->
          <v-col cols="1">
            <v-text-field
              label="ИНН"
              id="inn"
              v-model.number="inn"
            ></v-text-field>
          </v-col>

          <!-- address -->
          <v-col cols="2">
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
              @click="getClients"
              ><v-icon> mdi-table </v-icon></v-btn
            >
          </v-col>
        </v-row>
      </v-col>
      <v-col cols="3" v-if="clients.length">
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
          @change="setBankForClients(selectedBanks)"
        ></v-autocomplete>
        <!-- multiple -->
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
              :items="clients"
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
        <v-col cols="3">
          <div class="row">
            <v-card class="pa-5 w-100">
              Операторы
              <v-card-text class="scroll-y">
                <v-list>
                  <v-radio-group
                    @change="changeUserOfClients"
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
                      <v-btn
                        class="ml-3"
                        small
                        @click="getUserClients(user.id)"
                        :value="user.hmlids"
                        :disabled="disableuser == user.id"
                        >{{ user.hmlids }}</v-btn
                      >
                    </v-row>
                  </v-radio-group>
                </v-list>
              </v-card-text>
            </v-card>
          </div>
        </v-col>
      </v-row>
    </template>
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
      { text: "Оператор ID", value: "user_id" },
      { text: "Оператор", value: "fio" },
      { text: "Банк:Воронка", value: "banksfunnels" },
    ],
    clients: [],
    headers: [],
    message: "",
    snackbar: false,
  }),
  mounted() {
    this.getBanks();
    this.getUsers();
    this.getClientsWithoutBanks();
  },
  watch: {},
  methods: {
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
      let user_id = this.disableuser;
      send.user_id = this.userid;
      send.bank_id = bank_id;
      if (this.selected.length) {
        send.clients = this.selected.map((i) => i.id);
      } else {
        send.clients = this.clients.map((i) => i.id);
      }
      axios
        .post("/api/setBankForClients", send)
        .then((res) => {
          self.getUsers();
          self.getUserClients(user_id);

          self.selected = [];
          self.selectedBanks = 0;

          self.message =
            "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
          self.snackbar = true;
        })
        .catch((error) => console.log(error));
    },
    getUserClients(id) {
      let self = this;

      self.disableuser = id;
      axios
        .get("/api/getUserClients/" + id)
        .then((res) => {
          // console.log(res.data);
          self.clients = Object.entries(res.data).map((e) => e[1]);

           self.clients.map(function (e) {
             e.operator = self.users.find((u) => u.id == e.user_id).fio;
          //   e.date_created = e.created_at.substring(0, 10);
          //   e.provider = self.providers.find((p) => p.id == e.provider_id).name;
          //   if (e.status_id)
          //     e.status = self.statuses.find((s) => s.id == e.status_id).name;
           });
          // self.searchAll = "";
          self.selectedBanks = 0;
        })
        .catch((error) => console.log(error));
    },
    clickrow() {},
    changeUserOfClients() {
      let self = this;
      let send = {};
      send.user_id = this.userid;
      if (this.selected) {
        send.clients = this.selected.map((i) => i.id);
      } else {
        send.clients = this.clients.map((i) => i.id);
      }
      axios
        .post("/api/changeUserOfClients", send)
        .then((res) => {
          self.getUsers();
          self.getUserClients(self.userid);
          self.userid = null;
        })
        .catch((error) => console.log(error));
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
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data;
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
        })
        .catch((error) => console.log(error));
    },
  },
  components: {},
};
</script>

<style>
</style>
