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
const importexport = () => import("./importexport");
const users = () => import("./users");
// const statusLid = () => import("./statusLid");

// const providers = () => import("./providers");
// const mlids = () => import("../manager/mlids");
// const lids = () => import("../crmanager/lids");

export default {
  props:['user'],
  data: () => ({
    drawer: null,
    selectedItem: 0,

    items: [
      { text: "Импорт экспорт", name: "importexport", icon: "mdi-swap-vertical" },
      { text: "Пользователи", name: "users", icon: "mdi-account" },
      { text: "Воронки", name: "statusLid", icon: "mdi-format-list-checks" },
      //{ text: "Поставщики", name: "providers", icon: "mdi-contact-phone-outline" },
      // { text: "Рабочие места", name: "workPlaces", icon: "mdi-sitemap" },
      { text: "Распределение", name: "lids", icon: "mdi-account-arrow-left" },
      { text: "Управление", name: "mlids", icon: "mdi-phone-log-outline" },
    ],
    adminMenu: "importexport",
  }),
  computed: {
    adminComponent() {
      if (this.adminMenu == "importexport") return importexport;
       if (this.adminMenu == "users") return users;
    //   if (this.adminMenu == "statusLid") return statusLid;

    //   if (this.adminMenu == "providers") return providers;
    //   if (this.adminMenu == "mlids") return mlids;
    //   if (this.adminMenu == "lids") return lids;
    },
  },
  mounted:function (){

  },
  methods: {},
};
</script>
