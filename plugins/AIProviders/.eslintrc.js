module.exports = {
  rules: {
    // This plugin's Vue SFCs use the `<script setup>`-first ordering
    // (script before template), so the default tag-order check does not apply.
    'vue/component-tags-order': 'off',
  },
};
