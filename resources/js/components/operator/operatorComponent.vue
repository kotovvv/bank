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
          <v-subheader>Меню</v-subheader>
          <v-list-item-group v-model="selectedItem" color="primary">
            <v-list-item
              v-for="(item, i) in items"
              :key="i"
              @click="operatorMenu = item.name"
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
        <component :user="$props.user" :is="operatorComponent" />
        <!-- <tablenewlid></tablenewlid> -->
        <!-- </v-row> -->
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
const oClients = () => import("./oclients")
const oreport = () => import("./oreport");

export default {
  props: ["user"],
  data: () => ({
    drawer: null,
    selectedItem: 0,
    operatorMenu: "oClients",
    items: [
      { text: "Клиенты", name: "oClients", icon: "mdi-phone-log-outline" },
      { text: "Отчёты", name: "oreport", icon: "mdi-timetable" },
    ],
  }),
  computed: {
    operatorComponent() {
      if (this.operatorMenu == "oClients") return oClients;
      if (this.operatorMenu == "oreport") return oreport;
    },
  },
  methods: {},
};
</script>
