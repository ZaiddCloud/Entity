import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";

export default [
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    {
        files: ["resources/js/**/*.{js,vue}"],
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: {
                window: "readonly",
                document: "readonly",
                process: "readonly",
                module: "readonly",
            },
        },
        rules: {
            "vue/multi-word-component-names": "off",
            "no-unused-vars": "warn",
            "vue/no-unused-components": "warn",
        },
    },
];
