import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [react()],
  base: "/ett/",
  root: "web",
  build: {
    outDir: "../web-dist",
    emptyOutDir: true
  },
  server: {
    port: 5174
  }
});
