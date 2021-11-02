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
        <v-spacer></v-spacer>
      <v-col cols="3">
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
              :items="clients"
              ref="datatable"
            >
              <!-- :footer-props="{
                'items-per-page-options': [50, 10, 100, 250, 500, -1],
                'items-per-page-text': 'Показать',
              }" -->
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
      { text: "Банк:Воронка", value: "banksfunnels" },
    ],
    clients: [],
    headers: [],
    message: "",
    snackbar: false,
  }),
  mounted() {
    this.getBanks();
    // this.getUsers();
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

<style>
</style>
