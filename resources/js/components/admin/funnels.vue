<template>
  <v-card class="mx-auto" max-width="600">
    <v-data-table
      :headers="headers"
      :items="funnels"
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
          <v-toolbar-title>Воронки</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Добавить воронку
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
                    <v-col cols="4">
                      <v-text-field
                        v-model="editedItem.order"
                        label="Позиция"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="8">
                      <v-color-picker
                        class="ma-2"
                        canvas-height="100"
                        mode="hexa"
                        value="hexa"
                        v-model="editedItem.color"
                      ></v-color-picker>
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
        <v-btn color="primary" @click="getFunnels"> Обновить </v-btn>
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
    funnels: [],
    headers: [
      { text: "Наименование", value: "name" },
      { text: "Позиция", value: "order" },

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
      return this.editedIndex === -1 ? "Новая воронка" : "Редактировать ворону";
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
    this.getFunnels();
  },

  methods: {
    getFunnels() {
      let self = this;
      axios
        .get("/api/funnels")
        .then((res) => {
          self.funnels = res.data;
        })
        .catch((error) => console.log(error));
    },
    saveFunnels(funnel) {
      let self = this;
      axios
        .post("/api/funnels", funnel)
        .then((res) => {
          // console.log(res);
        })
        .catch((error) => console.log(error));
    },
    // initialize() {},
    editItem(item) {
      this.editedIndex = this.funnels.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.funnels.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      this.funnels.splice(this.editedIndex, 1);
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
        this.saveFunnels(this.editedItem);
        Object.assign(this.funnels[this.editedIndex], this.editedItem);
      } else {
        this.saveFunnels(this.editedItem);
        this.funnels.push(this.editedItem);
      }
      this.close();
    },
  },
};
</script>
