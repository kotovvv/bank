<template>
  <v-card class="mx-auto">
    <v-snackbar v-model="snackbar" top right :timeout="-1">
      <v-card-text v-html="message"></v-card-text>
      <template v-slot:action="{ attrs }">
        <v-btn color="pink" text v-bind="attrs" @click="snackbar = false">
          X
        </v-btn>
      </template>
    </v-snackbar>
    <!-- max-width="900" -->
    <v-data-table
      v-model="selected"
      :headers="headers"
      :items="users"
      sort-by="role_id"
      show-select
      class="elevation-1"
      :single-select="true"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Пользователи</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="500px">
            <template v-slot:activator="{ on, attrs }">
              <!-- <v-btn color="primary" dark class="mb-2 ml-2" @click="report">
                  Отчёт
                </v-btn> -->
              <statusUsers :o_users="selected" />
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Добавить пользователя
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
                        v-model="editedItem.fio"
                        label="ФИО"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="6">
                      <v-text-field
                        v-model="editedItem.name"
                        label="Логин"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="6">
                      <v-text-field
                        v-model="editedItem.password"
                        label="Пароль"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="6">
                      <v-select
                        :items="roles"
                        v-model="editedItem.role_id"
                        item-text="name"
                        item-value="id"
                        label="Роль"
                      ></v-select>
                    </v-col>
                    <v-col cols="6">
                      <v-select
                        :items="group"
                        v-model="editedItem.group_id"
                        item-text="fio"
                        item-value="id"
                        label="Группа"
                      ></v-select>
                    </v-col>
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
                >Уверены в удалении пользователя?</v-card-title
              >
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeDelete"
                  >Нет</v-btn
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
        <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="getUsers"> Reset </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import statusUsers from "./statusUsers";
import axios from "axios";
import _ from "lodash";
export default {
  props: ["user"],
  data: () => ({
    selected: [],
    dialog: false,
    dialogDelete: false,
    users: [],
    group: [{ fio: "Без группы", id: 0 }],
    roles: [
      { id: 1, name: "superAdmin" },
      { id: 2, name: "Administrator" },
      { id: 3, name: "Operator" },
    ],
    headers: [
      { text: "Логин", value: "name" },
      { text: "ФИО", value: "fio" },
      { text: "Роль", value: "role" },
      { text: "Группа", value: "group_id" },
      { text: "Действия", value: "actions", sortable: false },
    ],

    editedIndex: -1,
    editedItem: {
      name: "",
      fio: "",
      role_id: 0,
      password: "",
    },
    defaultItem: {
      name: "",
      fio: "",
      role_id: 0,
      password: "",
    },
    message: "",
    snackbar: false,
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "Новый пользователь"
        : "Редактировать пользователя";
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

  mounted() {
    // this.initialize(),
    this.getUsers();
  },

  methods: {
    rolename(user) {
      let self = this;
      user.role = self.roles.find((r) => r.id == user.role_id).name;
    },
    getUsers() {
      let self = this;
      axios
        .get("/api/users")
        .then((res) => {
          self.users = res.data;

          if (self.$props.user.role_id == 2) {
            self.roles = [self.roles[self.roles.findIndex((i) => i.id == 3)]];
            self.users = self.users.filter((u) => u.role_id == 3);
          }
          self.users.map(function (u) {
            self.rolename(u);
            if (u.role_id == 2) self.group.push(u);
          });
        })
        .catch((error) => console.log(error));
    },
    saveUsers(u) {
      let self = this;

      axios
        .post("/api/user/update", u)
        .then((res) => {
          this.dialog = false;
          this.getUsers();
        })
        .catch((error) => console.log(error));
    },
    initialize() {},
    editItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialogDelete = true;
    },

    deleteItemConfirm() {
      axios
        .delete("/api/user/" + this.editedItem.id)
        .then((res) => {
          console.log(res);
        })
        .catch((error) => console.log(error));
      this.users.splice(this.editedIndex, 1);
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
      this.message = "";
      this.snackbar = false;
      if (this.editedIndex > -1) {
        delete this.editedItem.role;
        this.saveUsers(this.editedItem);
        this.rolename(this.editedItem);
        Object.assign(this.users[this.editedIndex], this.editedItem);
      } else {
        if (_.find(this.users, { name: this.editedItem.name })) {
          this.message = "Такой логин уже есть";
          this.snackbar = true;
          return;
        }
        delete this.editedItem.role;
        this.saveUsers(this.editedItem);
        this.rolename(this.editedItem);
        this.users.push(this.editedItem);
      }
      this.close();
    },
  },
  components: {
    statusUsers,
  },
};
</script>
