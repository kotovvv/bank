<template>
  <div>
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
            <v-text-field label="В ФИО:" id="fio" v-model="fio"></v-text-field>
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
        <template v-if="clients.length">
        <v-row>
          <v-col cols="12">
            <v-data-table
              :headers="import_headers"
              item-key="id"
              :items="clients"
              ref="clients"
            >
            </v-data-table>
          </v-col>

        </v-row>
        </template>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data: () => ({
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
      { text: "", value: "id" },
      { text: "ИНН", value: "inn" },
      { text: "ФИО", value: "fullName" },
      { text: "Тел", value: "phoneNumber" },
      { text: "Наименование", value: "organizationName" },
      { text: "Адрес", value: "address" },
      { text: "Регион", value: "region" },
      { text: "Регистрация", value: "registration" },
    ],
    clients: [],
    headers: [],
    message: "",
  }),
  mounted() {

  },
  watch: {

  },
  methods: {

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
            }) => ({
              id,
              inn,
              fullName,
              phoneNumber,
              organizationName,
              address,
              region,
              registration,
            })
          );
        })
        .catch((error) => console.log(error));
    },
  },
  components: {

  },
};
</script>

<style>

</style>
