<template>
  <v-app id="inspire">

    <v-app-bar app>
              <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 379 462"
	 style="enable-background:new 0 0 379 462;width:38px" xml:space="preserve">
	<path d="M258,232.6c31.4-20.9,45.1-42.5,62.8-90c6.6-17.7,11.5-37.3,15.2-57.5c1.4-7.8,2.7-20,9.8-18
		c8.8,2.5,5.9,26.4,5.8,38.8c-0.6,46.2-0.2,92.7,0.1,138.9c0.3,33.6-2.8,78.8-4.5,113.2c-0.6,12.6-1.3,25.1-2,37.6
		c-0.7-12.5-1.4-25.1-2.1-37.6c-2-34.9-5.9-78.9-6.1-112.9c-0.3-32.5-0.5-65.1-0.4-97.7c-0.3,0.8-0.6,1.7-0.8,2.5
		C318.5,199.8,289.7,223,258,232.6L258,232.6z"/>
	<path d="M177.3,218.6c16.6,16.6,21.2,29.2,30.6,50.5c10.7,24.3,37.2,99.3,71,93.1c8.1-1.5,17.1-7.7,24.5-11.7
		c-5.3,8.6-7,11.8-16.2,17c-56.4,31.6-94-103.8-123-130.3c-5.1-3.6-6.3-10.7-2.7-15.8C165.1,216.3,172.2,215,177.3,218.6z"/>
	<path d="M233.2,232.6c-32.9-20.9-47.3-42.5-65.9-90c-6.9-17.7-12.1-37.3-15.9-57.5c-1.5-7.8-2.8-20-10.2-18
		c-9.2,2.5-6.2,26.4-6.1,38.8c0.6,46.2,0.2,92.7-0.1,138.9c-0.3,33.6,2.9,78.8,4.8,113.2c0.7,12.6,1.4,25.1,2.1,37.6
		c0.7-12.5,1.4-25.1,2.2-37.6c2.1-34.9,6.2-78.9,6.4-112.9c0.3-32.5,0.5-65.1,0.4-97.7c0.3,0.8,0.6,1.7,0.9,2.5
		C169.7,199.8,199.9,223,233.2,232.6L233.2,232.6z"/>
	<path d="M170.8,227.9c44.9-25.9,91-72,76.4-128.6C229.6,31.2,140.5,28.5,95.1,75.9c-76.7,80-58.6,286.2,43.4,340.2
		c62.1,32.9,127.8-6.9,161.3-60.9l5.8-10.2c-22.5,49.1-63.5,92.8-120.2,97.5C14.7,456.4-29.2,127.6,100.6,41.8
		c60-39.7,149-23.5,161.6,54.5C272.3,158.9,222.7,203.9,170.8,227.9L170.8,227.9z"/>
</svg>

      <!-- <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon> -->
<v-bottom-navigation
 color="primary"
 background-color="transparent"
  :value="managerMenu"
 style="box-shadow:none"
 >
    <v-btn :value="item.name"
    v-for="(item, i) in items"
              :key="i"
    @click="managerMenu = item.name"
    >
      <span>{{item.text}}</span>

      <v-icon>{{item.icon}}</v-icon>
    </v-btn>

  </v-bottom-navigation>
      <v-toolbar-title>{{ user.fio }}</v-toolbar-title>

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
const importcsv = () => import("../admin/importcsv");
const users = () => import("../admin/users");
const setBank = () => import("./setBank");
const setOperator = () => import("./setOperator");
const report = () => import("../admin/report");

export default {
  props: ["user"],
  data: () => ({
    drawer: null,
    selectedItem: 0,

    items: [
      { text: "Импорт", name: "importcsv", icon: "mdi-swap-vertical" },
      { text: "Основная база", name: "setBank", icon: "mdi-database" },
      { text: "Банки", name: "setOperator", icon: "mdi-playlist-plus" },
      { text: "Операторы", name: "users", icon: "mdi-account-multiple" },

      { text: "Отчёты", name: "report", icon: "mdi-timetable" },

    ],
    managerMenu: "setBank",
  }),
  computed: {
    managerComponent() {
      if (this.managerMenu == "importcsv") return importcsv;
      if (this.managerMenu == "dictionary") return dictionary;
      if (this.managerMenu == "users") return users;
      if (this.managerMenu == "setOperator") return setOperator;
      if (this.managerMenu == "setBank") return setBank;
      if (this.managerMenu == "report") return report;
    },
  },
  mounted: function () {},
  methods: {},
};
</script>
