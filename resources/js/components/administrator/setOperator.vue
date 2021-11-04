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
            <v-toolbar
              color="primary"
              dark
            ><v-icon
      large
      color="darken-2"
    >
      mdi-alert-outline
    </v-icon></v-toolbar>
            <v-card-text>
              <div class="text-h4 pa-12">Точно удалить выбранный банк из {{selected.length}}  {{plueral(selected.length,['выделенной строки','выделенных строк','выделенных строк'])}} ?</div>
            </v-card-text>
            <v-card-actions class="justify-end">
                              <v-btn
                @click="delBankFromClients(selectedBank);dialog = false"
              >Да</v-btn>
              <v-spacer></v-spacer>
              <v-btn
                text
                @click="dialog = false;selectedBanks=0"
              >Нет</v-btn>
            </v-card-actions>
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
            ></v-autocomplete>
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
                  @click="dateAdd = [];
                    $refs.dialog2.save(dateAdd);
                    modal2 = false;"
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
          <!-- status -->
          <v-col cols="3">
            <v-autocomplete
              v-model="selectedFunnel"
              :items="funnels"
              outlined
              dense
              chips
              small-chips
              item-text="name"
              item-value="id"
              label="Статус"
            ></v-autocomplete>
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
          <v-spacer></v-spacer>

          <!-- del bank -->
          <v-col v-if="selected.length && selectedBank">
            <v-btn
              color="primary"
              elevation="2"
              outlined
              raised
              @click="dialog = true"
              >удалить банк</v-btn
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
              <template v-if="clients.length">
                <v-text-field
                  label="Введите число"
                  v-model.number.lazy="hmrow"
                  @input="selectRow"
                  :max="clients.length"
                  :messages="'Сколько записей пометить?'"
                ></v-text-field>
                <v-card-text
                  >из: <span class="text-h5">{{ clients.length }}</span> и назначить оператору</v-card-text
                >
              </template>
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
    dialog:false,
  }),
  mounted() {
    this.getBanks();
    this.getFunnels();
    this.getUsers();
  },
  watch: {},
  methods: {
      delBankFromClients(bank_id){
          let self = this
          let send = {}
          send.bank_id = bank_id
          send.clients = this.selected.map(i => {return i.id})
      axios
        .post("/api/delBankFromClients",send)
        .then((res) => {
            if(self.disableuser){
                 self.getUserClients(self.disableuser)
            }else{
                self.getClients()
            }
self.message =
            "Записей: " + res.data.all + "<br>Изменено: " + res.data.done;
          self.snackbar = true;
          self.selected = []
        })
        .catch((error) => console.log(error));

      },
      plueral(number, words){
    return words[(number % 100 > 4 && number % 100 < 20) ? 2 : [2, 0, 1, 1, 1, 2][(number % 10 < 5) ? Math.abs(number) % 10 : 5]];
      },
      selectRow(){
          this.selected = this.clients.slice(0,this.hmrow)
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
      send.user_id = this.userid;
      if (this.selected.length) {
        send.clients = this.selected.map((i) => i.id);
      } else {
        send.clients = this.clients.map((i) => i.id);
      }
      axios
        .post("/api/changeUserOfClients", send)
        .then((res) => {
          self.getUsers();
          self.getClients()
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
          self.hmrow=''
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
      if (this.selectedBank) send.bank_id = this.selectedBank;
      if (this.selectedFunnel) send.funnel_id = this.selectedFunnel;
      send.user_id = 0
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
