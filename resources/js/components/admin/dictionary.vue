<template>
  <div>
    <v-tabs v-model="tab">
      <v-tab>Пользователи</v-tab>
      <v-tab>Банки</v-tab>
      <v-tab>Воронки</v-tab>
    </v-tabs>
    <v-tabs-items v-model="tab">
      <v-tab-item>
        <users :user="$props.user"></users>
      </v-tab-item>
      <v-tab-item>
        <banks></banks>
      </v-tab-item>
      <v-tab-item>
        <funnel></funnel>
      </v-tab-item>
    </v-tabs-items>
  </div>
</template>

<script>
import users from "./users";
import funnel from "./funnels";
import banks from "./banks.vue";
export default {
  props: ["user"],
  data: () => ({
    tab: null,
  }),
  components: {
    users,
    funnel,
    banks,
  },
};
</script>
