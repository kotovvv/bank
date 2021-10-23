<template>
  <div>
    <v-snackbar v-model="message.length" top right timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="message = ''">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <v-row>
      <v-col cols="12">
        <div
          id="drop"
          @drop="handleDrop"
          @dragover="handleDragover"
          @dragenter="handleDragover"
        >
          Перетяните файл сюда
        </div>
      </v-col>
    </v-row>

    <v-main>
        <v-row>
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
              v-model="dateReg"
              label="Регистрация (период)"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="attrs"
              v-on="on"
                  ></v-text-field>
          </template>
          <v-date-picker
            v-model="dateReg"
            scrollable
            range
            locale="ru-ru"
          >
            <v-spacer></v-spacer>
            <v-btn
              text
              color="primary"
              @click="modal = false"
            >
              Отменить
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
              v-model="dateAdd"
              label="Заливка (период)"
              prepend-icon="mdi-calendar"
              readonly
              v-bind="attrs"
              v-on="on"
            ></v-text-field>
          </template>
          <v-date-picker
            v-model="dateAdd"
            scrollable
            range
            locale="ru-ru"
          >
            <v-spacer></v-spacer>
            <v-btn
              text
              color="primary"
              @click="modal2 = false"
            >
              Отменить
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

            </v-col>

            <!-- fio -->
            <v-col cols="2"></v-col>

            <!-- inn -->
            <v-col cols="2"></v-col>

            <!-- address -->
            <v-col cols="2"></v-col>

            <!-- reg -->
            <v-col cols="2"></v-col>

        </v-row>

      <v-row>
               <v-col cols="12">
        <v-data-table
          :headers="import_headers"
          item-key="id"
          :items="clients"
          ref="importtable"
        >
          <template v-slot:item.id="{ item }"> </template>
        </v-data-table>
      </v-col>
        <v-col cols="12">
          <v-btn
            color="primary"
            elevation="2"
            outlined
            raised
            x-large
            @click="exportfile"
            >Скачать базу</v-btn
          >
        </v-col>
      </v-row>
      <v-row> </v-row>
    </v-main>
  </div>
</template>

<script>
import XLSX from "xlsx";
import axios from "axios";
export default {
  data: () => ({
      dateReg:[],
      dateAdd:[],
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
    this.getClients();
  },
  methods: {
    exportfile() {
      let newWindow = window.open();
      axios
        .get("api/export")
        .then(function (response) {
          newWindow.location =
            "http://" + window.location.hostname + "/api/export";
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    importfile() {
      let self = this;
      let send = {};
      send.headers = self.headers;
      send.clients = self.clients;

      axios
        .post("api/import", send)
        .then(function (response) {
          //   console.log(response);
          self.message =
            "Всего: " +
            response.data.all +
            "<br>Дубликатов: " +
            response.data.duplicate +
            "<br>Добавлено: " +
            response.data.inserted;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    handleDragover(e) {
      e.stopPropagation();
      e.preventDefault();
      e.dataTransfer.dropEffect = "copy";
    },
    handleDrop(e) {
      e.stopPropagation();
      e.preventDefault();
      let self = this;
      //   console.log("DROPPED");
      var files = e.dataTransfer.files,
        i,
        f;
      for (i = 0, f = files[i]; i != files.length; ++i) {
        var reader = new FileReader(),
          name = f.name;
        reader.onload = function (e) {
          var results,
            data = e.target.result,
            fixedData = self.fixdata(data),
            workbook = XLSX.read(btoa(fixedData), { type: "base64" }),
            firstSheetName = workbook.SheetNames[0],
            worksheet = workbook.Sheets[firstSheetName];
          self.headers = self.get_header_row(worksheet);
          results = XLSX.utils.sheet_to_json(worksheet, {
            raw: false,
            dateNF: "YYYY-M-D",
          });
          self.clients = results;
          if (self.clients.length > 0) {
            self.importfile();
          }
        };
        reader.readAsArrayBuffer(f);
      }
    },
    workbook_to_json(workbook) {
      var result = {};
      workbook.SheetNames.forEach(function (sheetName) {
        var roa = XLSX.utils.sheet_to_row_object_array(
          workbook.Sheets[sheetName]
        );
        if (roa.length > 0) {
          result[sheetName] = roa;
        }
      });
      return result;
    },
    fixdata(data) {
      var o = "",
        l = 0,
        w = 10240;
      for (; l < data.byteLength / w; ++l)
        o += String.fromCharCode.apply(
          null,
          new Uint8Array(data.slice(l * w, l * w + w))
        );
      o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
      return o;
    },
    get_header_row(sheet) {
      var headers = [],
        range = XLSX.utils.decode_range(sheet["!ref"]);
      var C,
        R = range.s.r; /* start in the first row */
      for (C = range.s.c; C <= range.e.c; ++C) {
        /* walk every column in the range */
        var cell =
          sheet[
            XLSX.utils.encode_cell({ c: C, r: R })
          ]; /* find the cell in the first row */
        var hdr = "UNKNOWN " + C; // <-- replace with your desired default
        if (cell && cell.t) hdr = XLSX.utils.format_cell(cell);
        headers.push(hdr);
      }
      return headers;
    },
    getClients() {
      const self = this;
      axios
        .get("/api/getClients")
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
};
</script>

<style>
#drop {
  border: 2px dashed #bbb;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  border-radius: 5px;
  padding: 25px;
  text-align: center;
  font: 20pt bold, "Roboto";
  color: #bbb;
}
</style>
