<template>
  <div>
    <v-snackbar v-model="snackbar" top right timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <v-row>
      <v-spacer></v-spacer>
      <v-col cols="2">
        <download-csv :data="demo" delimiter=";" :name="'Clients.csv'">
          <v-btn depressed>Скачать пример</v-btn>
          <v-icon> mdi-download-circle </v-icon>
        </download-csv>
      </v-col>
    </v-row>
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
    <v-row>
      <v-col cols="12">
        <v-data-table
          :headers="import_headers"
          item-key="id"
          :items="clients"
          ref="importtable"
        >
        </v-data-table>
      </v-col>
    </v-row>
  </div>
</template>


<script>
import XLSX from "xlsx";
import axios from "axios";
import JsonCSV from "vue-json-csv";
export default {
  data: () => ({
    clients: [],
    headers: [],
    snackbar: false,
    message: "",
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
    demo: [{
      inn: "11111111111",
      fullName: "ЮПОВА ЕЛЕНА СЕРГЕЕВНА",
      phoneNumber: "79999999999",
      organizationName: "ИП ЮПОВА ЕЛЕНА СЕРГЕЕВНА",
      address: "452379,БАШКОРТОСТАН РЕСПУБЛИКА,КАРАИДЕЛЬСКИЙ РАЙОН,УСТЬ-БАРТАГА ДЕРЕВНЯ,СОЛНЕЧНАЯ ПОЛЯНА УЛИЦА",
      region: "БАШКОРТОСТАН",
      registration: "2021-10-01"
    }],
  }),
  methods: {
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
            self.snackbar=true;
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
  },
  components: {
    downloadCsv: JsonCSV,
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
