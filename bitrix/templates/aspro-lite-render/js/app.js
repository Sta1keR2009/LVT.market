BX.namespace("Aspro");

BX.Aspro.Utils = {
  isFunction: (func) => {
    return typeof func == "function" || typeof window[func] === "function";
  },

  readyDOM: (callback) => {
    if (document.readyState !== "loading") {
      callback();
    } else {
      document.addEventListener("DOMContentLoaded", callback);
    }
  },
};

BX.namespace("Aspro.Loader");
BX.namespace("Aspro.Observer");

(() => {
  /**
   * class JAsproLoader
   */

  const addedCss = [];
  const addedJs = [];
  const addedExt = [];

  class JAsproLoader {
    constructor() {}

    addCss(...items) {
      return new Promise((resolve, reject) => {
        items = items.filter((css) => {
          return BX.type.isString(css) && !~addedCss.indexOf(css);
        });

        if (items.length) {
          BX.loadCSS(items, () => {
            items.forEach((css) => {
              addedCss.push(css);
            });

            resolve();
          });
        } else {
          resolve();
        }
      });
    }

    addJs(...items) {
      return new Promise((resolve, reject) => {
        items = items.filter((js) => {
          return BX.type.isString(js) && !~addedJs.indexOf(js);
        });

        if (items.length) {
          BX.loadScript(items, () => {
            items.forEach((js) => {
              addedJs.push(js);
            });

            resolve();
          });
        } else {
          resolve();
        }
      });
    }

    addExt(...extensions) {
      return new Promise((resolve, reject) => {
        extensions = extensions.map((extension) => {
          return `aspro_${extension}`;
        });

        extensions = extensions.filter((extension) => {
          return BX.type.isString(extension) && !~addedExt.indexOf(extension);
        });

        if (extensions.length) {
          BX.loadExt(extensions).then(() => {
            extensions.forEach((extension) => {
              addedExt.push(extension);
            });

            resolve();
          });
        } else {
          resolve();
        }
      });
    }

    added(item) {
      if (BX.type.isString(item) && item) {
        return !!~addedCss.indexOf(item) || !!~addedJs.indexOf(item) || !!~addedExt.indexOf(`aspro_${item}`);
      } else {
        return {
          ext: addedExt,
          css: addedCss,
          js: addedJs,
        };
      }
    }

    add(params) {
      params = BX.type.isObject(params) ? params : {};
      let extensions = params.ext ? (BX.type.isArray(params.ext) ? params.ext : [params.ext]) : [];
      let cssItems = params.css ? (BX.type.isArray(params.css) ? params.css : [params.css]) : [];
      let jsItems = params.js ? (BX.type.isArray(params.js) ? params.js : [params.js]) : [];

      return new Promise((resolve, reject) => {
        this.addExt(...extensions).then(() => {
          this.addCss(...cssItems).then(() => {
            this.addJs(...jsItems).then(() => {
              resolve();
            });
          });
        });
      });
    }

    once(params) {
      params = BX.type.isObject(params) ? params : {};

      params.appear = BX.type.isString(params.appear)
        ? [params.appear]
        : BX.type.isArray(params.appear)
        ? params.appear
        : null;
      params.add = BX.type.isObject(params.add) ? params.add : null;

      return new Promise((resolve, reject) => {
        if (params.appear) {
          BX.Aspro.Observer.appear(...params.appear).then(() => {
            if (params.add) {
              this.add(params.add).then(resolve);
            } else {
              resolve();
            }
          });
        } else if (params.add) {
          this.add(params.add).then(resolve);
        } else {
          resolve();
        }
      });
    }
  }

  BX.Aspro.Loader = new JAsproLoader();

  /**
   * class JAsproObserver
   */

  const intersectionConfig = {
    root: null,
    rootMargin: "0px",
    threshold: 0,
  };

  class JAsproObserver {
    constructor() {
      this.mkIntersectionConfig();
    }

    mkIntersectionConfig(config) {
      if (BX.type.isObject(config)) {
        intersectionConfig.root = config.root;
        intersectionConfig.rootMargin = config.rootMargin;
        intersectionConfig.threshold = config.threshold;
      } else {
        intersectionConfig.rootMargin = "0px";
      }
    }

    appear(node, config) {
      config = BX.type.isObject(config) && config ? config : intersectionConfig;

      return new Promise((resolve, reject) => {
        if (BX.type.isString(node)) {
          let nodes = document.querySelectorAll(node);
          if (nodes.length) {
            let observer = new IntersectionObserver((entries, observer) => {
              entries.forEach((entry) => {
                if (entry.isIntersecting) {
                  observer.unobserve(entry.target);

                  resolve();
                }
              });
            }, config);

            nodes.forEach((node) => {
              observer.observe(node);
            });
          } else {
            resolve();
          }
        } else if (BX.type.isDomNode(node)) {
          new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                observer.unobserve(entry.target);

                resolve();
              }
            });
          }, config).observe(node);
        } else {
          resolve();
        }
      });
    }
  }

  BX.Aspro.Observer = new JAsproObserver();
})();
