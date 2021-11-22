<template>
  <v-app id="inspire">
    <!-- <v-system-bar app>
      <v-spacer></v-spacer>

      <v-icon>mdi-square</v-icon>

      <v-icon>mdi-circle</v-icon>

      <v-icon>mdi-triangle</v-icon>
    </v-system-bar> -->

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
              @click="managerMenu = item.name"
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
        <component :user="$props.user" :is="managerComponent" />
        <!-- <tablenewlid></tablenewlid> -->
        <!-- </v-row> -->
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
const importxls = () => import("../admin/importxls");
const dictionary = () => import("../admin/dictionary");
const setBank = () => import("./setBank");
const setOperator = () => import("./setOperator");
const report = () => import("../admin/report");

export default {
  props: ["user"],
  data: () => ({
    drawer: null,
    selectedItem: 0,

    items: [
      { text: "Импорт", name: "importxls", icon: "mdi-swap-vertical" },
      { text: "Основная база", name: "setBank", icon: "mdi-database" },
      { text: "Банки", name: "setOperator", icon: "mdi-playlist-plus" },
      { text: "Отчёты", name: "report", icon: "mdi-timetable" },

    ],
    managerMenu: "setBank",
  }),
  computed: {
    managerComponent() {
      if (this.managerMenu == "importxls") return importxls;
      if (this.managerMenu == "dictionary") return dictionary;
      if (this.managerMenu == "setOperator") return setOperator;
      if (this.managerMenu == "setBank") return setBank;
      if (this.managerMenu == "report") return report;
    },
  },
  mounted: function () {},
  methods: {},
};
</script>
