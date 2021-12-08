<template>
  <v-card class="mx-auto" max-width="600">
    <v-data-table
      :headers="headers"
      :items="Banks"
      sort-by="role_id"
      class="elevation-1"
      cols="6"
    >
      <template v-slot:item.name="{ item }">
        <v-chip :style="{'background':item.color}">
          {{ item.name }}
        </v-chip>
      </template>
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Банки</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Добавить Банк
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.name"
                        label="Наименование"
                      ></v-text-field>
                    </v-col>
                     <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.url"
                        label="Ссылка"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12">
                      <v-text-field
                        v-model="editedItem.token"
                        label="Key"
                      ></v-text-field>
                    </v-col>
                   
                    <v-col cols="4">
                      <v-text-field
                        v-model="editedItem.abbr"
                        label="Аббревиатура"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="4"> </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">
                  Отмена
                </v-btn>
                <v-btn color="blue darken-1" text @click="save">
                  Сохранить
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="headline"
                >Увенены в удалении?</v-card-title
              >
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeDelete"
                  >Отмена</v-btn
                >
                <v-btn color="blue darken-1" text @click="deleteItemConfirm"
                  >Да</v-btn
                >
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon small class="mr-2" @click="editItem(item)"> mdi-pencil </v-icon>
        <!-- <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon> -->
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="getBanks"> Обновить </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import axios from "axios";
export default {
  data: () => ({
    dialog: false,
    dialogDelete: false,
    Banks: [],
    headers: [
      { text: "Наименование", value: "name" },

      { text: "Действия", value: "actions", sortable: false },
    ],

    editedIndex: -1,
    editedItem: {
      name: "",
      color:'#fff',
      order:0
    },
    defaultItem: {
      name: "",
      color:'#fff',
      order:0
    },
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Новый банк" : "Редактировать банк";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
  },

  created() {
    this.getBanks();
  },

  methods: {
    getBanks() {
      let self = this;
      axios
        .get("/api/banks")
        .then((res) => {
          self.Banks = res.data;
        })
        .catch((error) => console.log(error));
    },
    saveBanks(bank) {
      let self = this;
      axios
        .post("/api/banks", bank)
        .then((res) => {
          // console.log(res);
        })
        .catch((error) => console.log(error));
    },
    // initialize() {},
    editItem(item) {
      this.editedIndex = this.Banks.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.Banks.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      this.Banks.splice(this.editedIndex, 1);
      this.closeDelete();
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    closeDelete() {
      this.dialogDelete = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      if (this.editedIndex > -1) {
        this.saveBanks(this.editedItem);
        Object.assign(this.Banks[this.editedIndex], this.editedItem);
      } else {
        this.saveBanks(this.editedItem);
        this.Banks.push(this.editedItem);
      }
      this.close();
    },
  },
};
</script>
