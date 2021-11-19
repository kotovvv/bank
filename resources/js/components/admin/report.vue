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
              :name="period.join()+'.csv'"
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
              <tr class="py-1" v-for="(tr, i) in td_body" :key="i">
                <td
                  v-for="(td, itd) in tr"
                  :key="itd"
                  :class="{ 'font-weight-bold': itd == 0 }"
                >
                  {{ td }}
                </td>
              </tr>
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
  }),
  mounted() {
    this.getBanks();
  },
  methods: {
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
          self.td_body = res.data.td;
        })
        .catch((error) => console.log(error));
    },
  },
  components: {
    "download-csv": JsonCSV,
  },
};
</script>
