<template>
  <v-app id="inspire">
    <v-app-bar app>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

      <v-toolbar-title>Пользователь в системе: {{ user.fio }}</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn @click="$emit('login', {})">ВЫХОД</v-btn>
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      fixed
      temporary
      @click="drawer = !drawer"
    >
      <!-- menu -->
      <v-card class="mx-auto" max-width="300" tile @click="drawer = !drawer">
        <v-list>
          <v-subheader>MENU</v-subheader>
          <v-list-item-group v-model="selectedItem" color="primary">
            <v-list-item
              v-for="(item, i) in items"
              :key="i"
              @click="adminMenu = item.name"
            >
              <v-list-item-icon>
                <v-icon v-text="item.icon"></v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title v-text="item.text"></v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-card>
    </v-navigation-drawer>

    <v-main class="grey lighten-2">
      <v-container fluid>
        <!-- <v-row> -->
        <!-- table -->
        <component :user="$props.user" :is="adminComponent" />
        <!-- <tablenewlid></tablenewlid> -->
        <!-- </v-row> -->
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
const importxls = () => import("./importxls");
const dictionary = () => import("./dictionary");
const setBank = () => import("../administrator/setBank");
const setOperator = () => import("../administrator/setOperator");
const oclients = () => import("../operator/oclients");
const report = () => import("./report");

export default {
  props: ["user"],
  data: () => ({
    drawer: null,
    selectedItem: 0,

    items: [
      { text: "Импорт", name: "importxls", icon: "mdi-swap-vertical" },
      { text: "Основная база", name: "setBank", icon: "mdi-database" },
      { text: "Банки", name: "setOperator", icon: "mdi-playlist-plus" },
      { text: "Оператор", name: "oclients", icon: "mdi-phone-log-outline" },
      { text: "Отчёты", name: "report", icon: "mdi-timetable" },
      {
        text: "Справочники",
        name: "dictionary",
        icon: "mdi-format-list-checks",
      },
    ],
    adminMenu: "report",
  }),
  computed: {
    adminComponent() {
      if (this.adminMenu == "importxls") return importxls;
      if (this.adminMenu == "dictionary") return dictionary;
      if (this.adminMenu == "setOperator") return setOperator;
      if (this.adminMenu == "setBank") return setBank;
      if (this.adminMenu == "oclients") return oclients;
      if (this.adminMenu == "report") return report;
    },
  },
  mounted: function () {},
  methods: {},
};
</script>
