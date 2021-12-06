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
          <!-- period -->
          <v-col cols="2">
            <v-dialog
              ref="dialog"
              v-model="modal"
              :return-value.sync="period"
              persistent
              width="290px"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model.lazy="period"
                  label="Период"
                  readonly
                  v-bind="attrs"
                  v-on="on"
                  id="period"
                ></v-text-field>
              </template>
              <v-date-picker
                v-model.lazy="period"
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
                    period = [];
                    $refs.dialog.save(period);
                    modal = false;
                  "
                >
                  Очистить
                </v-btn>
                <v-btn text color="primary" @click="$refs.dialog.save(period)">
                  Выбрать
                </v-btn>
              </v-date-picker>
            </v-dialog>
          </v-col>
          <!-- btn -->
          <v-col cols="1">
            <v-btn
              color="primary"
              elevation="2"
              outlined
              raised
              @click="getReportAll"
              ><v-icon> mdi-table-large </v-icon></v-btn
            >
          </v-col>
          <v-spacer></v-spacer>
          <v-col v-if="td_body.length">
            <download-csv
              delimiter=";"
              :data="[td_head].concat(td_body)"
              :name="period.join() + '.csv'"
            >
              <v-btn depressed>Скачать отчёт CSV</v-btn>
            </download-csv>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-simple-table>
          <template v-slot:default>
            <thead>
              <tr>
                <th class="pa-1" v-for="(th, i) in td_head" :key="i">
                  {{ th }}
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="b in banks">
                <tr
                  class="bank blue lighten-4"
                  @click="toggleUser(b.id)"
                >
                  <td>{{ b.name }}</td>
                  <td v-for="(f, fi) in funnels" :key="fi">
                    {{ countb(b.id, f.id) }}
                  </td>
                </tr>
                <template v-for="u in users">
                  <tr
                    :key="u.id + b.id"
                    class="user "
                    :class="'bank' + b.id"
                    v-if="userInBank(u.id, b.id)"
                  >
                    <td class="text-right">{{ u.fio }}</td>
                    <td v-for="(f, fi) in funnels" :key="fi">
                      {{ countf(u.id, b.id, f.id) }}
                    </td>
                  </tr>
                </template>
              </template>
              <template v-for="(tr, i) in td_body">
                <tr class="py-1" :key="'trb' + i">
                  <td
                    v-for="(td, inx) in tr"
                    :key="'1tr-' + inx"
                    :class="{ 'font-weight-bold': itd == 0 }"
                  >
                    {{ td }}
                  </td>
                </tr>
              </template>
            </tbody>
          </template>
        </v-simple-table>
      </v-col>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
import JsonCSV from "vue-json-csv";
import lodash from "lodash";
export default {
  data: () => ({
    period: [],
    dialog: false,
    modal: false,
    snackbar: false,
    banks: [],
    selectedBank: 0,
    message: "",
    td_head: [],
    td_body: [],
    users: [],
    banks: [],
    funnels: [],
    ubf: [],
  }),
  mounted() {
    this.getBanks();
  },
  methods: {
    toggleUser(bank_id){
const bid = document.querySelectorAll('.bank'+bank_id)
for(let i = 0;i <= bid.length;i++){
  bid[i].classList.toggle('show')
}
    },
    userInBank(user_id, bank_id) {
      return lodash.filter(
        this.ubf,
        (i) => i.user_id == user_id && i.bank_id == bank_id
      ).length;
    },
    countb(bank_id, funnel_id) {
      let hm = lodash.filter(
        this.ubf,
        (i) => i.funnel_id == funnel_id && i.bank_id == bank_id
      ).length;
      return hm > 0 ? hm : "";
    },
    countf(user_id, bank_id, funnel_id) {
      let hm = lodash.filter(
        this.ubf,
        (i) =>
          i.user_id == user_id &&
          i.funnel_id == funnel_id &&
          i.bank_id == bank_id
      ).length;
      return hm > 0 ? hm : "";
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
          self.banks.unshift({ id: 0, name: "Все" });
        })
        .catch((error) => console.log(error));
    },
    getReportAll() {
      let self = this;
      let send = {};
      send.bank_id = self.selectedBank;
      if (self.period == "") {
        self.message = "Установите период";
        self.snackbar = true;
        return;
      }
      send.period = self.period;
      axios
        .post("/api/getReportAll", send)
        .then((res) => {
          self.td_head = res.data.header;
          // self.td_body = res.data.td;
          self.users = res.data.users;
          self.funnels = res.data.funnels;
          self.banks = res.data.banks;
          self.ubf = res.data.ubf;
        })
        .catch((error) => console.log(error));
    },
  },
  components: {
    "download-csv": JsonCSV,
  },
};
</script>

<style scoped>
.user{display: none;}
.user.show{display: revert;}
</style>