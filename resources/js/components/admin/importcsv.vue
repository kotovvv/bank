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
        <div id="selectFile" class="mt-10">
            Выбор для загрузки<input type="file" @change="handleDrop">
        </div>
      </v-col>

    </v-row>
    <v-row>
      <v-col cols="12">
                  <v-progress-linear
          :active="loading"
          :indeterminate="loading"
          color="deep-purple accent-4"
        ></v-progress-linear>
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

import axios from "axios";
import JsonCSV from "vue-json-csv";
export default {
  data: () => ({
      loading:false,
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
self.loading=true
      axios
        .post("api/import", send)
        .then(function (response) {
          //   console.log(response);
          self.loading=false
          self.message =
            "Всего: " +
            response.data.all +
            "<br>Дубликатов: " +
            response.data.duplicate +
            "<br>Ошибок: " +
            response.data.error +
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

      const ftype = [
        "text/comma-separated-values",
        "text/csv",
        "application/csv",
        "application/excel",
        "application/vnd.ms-excel",
        "application/vnd.msexcel",
        "text/anytext",
        "text/plain",
      ];
        //  console.log("DROPPED");
      var files = e.target.files || e.dataTransfer.files,
        i,
        f;
      for (i = 0, f = files[i]; i != files.length; ++i) {
             if (f == null) return
     if (ftype.indexOf(f.type) >= 0) {
        this.createInput(f);
      }
      }
    },
    csvJSON(csv) {
      var vm = this;
      var lines = csv.split("\n");
      var result = [];
      var headers = lines[0].split(";");
      headers[headers.length - 1] = headers[headers.length - 1].trim()
      // headers = [ "inn", "fullName", "phoneNumber", "organizationName", "address", "region", "registration"];
      // vm.parse_header = lines[0].split(",");
      // lines[0].split(",").forEach(function (key) {
      //   vm.sortOrders[key] = 1;
      // });

      lines.map(function (line, indexLine) {
        if (indexLine < 1) return; // Jump header line

        var obj = {};
        line = line.trim();
        var currentline = line.split(";");

        headers.map(function (header, indexHeader) {
          obj[header] = currentline[indexHeader];
        });
        result.push(obj);
      });

      result.pop(); // remove the last item because undefined values

      return result; // JavaScript object
    },
    createInput(file) {
      let promise = new Promise((resolve, reject) => {
        var reader = new FileReader();
        var vm = this;
        reader.onload = (e) => {
          resolve((vm.fileinput = reader.result));
        };
        reader.readAsText(file);
      });

      promise.then(
        (result) => {
          let vm = this;
          /* handle a successful result */
          // console.log(vm.csvJSON(this.fileinput));
          // reader.onload = function(event) {
          // arr.filter((v,i,a)=>a.findIndex(t=>(t.id === v.id))===i)
          vm.clients = vm.csvJSON(this.fileinput)
          vm.importfile()
          // };
        },
        (error) => {
          /* handle an error */
          console.log(error);
        }
      );
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

#selectFile{
    margin:auto;
    padding: 10px 20px;
    border:2px solid grey;
    max-width: 200px;
    position:relative
    }
#selectFile input {
  position: absolute;
  cursor: pointer;
  top: 0px;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
}
</style>
