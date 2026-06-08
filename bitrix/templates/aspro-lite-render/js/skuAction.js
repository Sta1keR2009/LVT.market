(function () {
  this.SkuItemProps = function () {};

  const ready = function (callback) {
    if (document.readyState !== "loading") {
      callback();
    } else {
      document.addEventListener("DOMContentLoaded", callback);
    }
  };
  console.log(333);
  const clickHandler = function () {
    const $skuContainer = document.querySelector(".sku-props");
    if ($skuContainer) {
      $skuContainer.addEventListener("click", function (e) {
        const $item = e.target.closest(".sku-props__value");
        if (!$item) {
          return;
        }
        if ($item.classList.contains("sku-props__value--active")) {
          return;
        }

        //set text in group title
        console.dir(this.querySelectorAll(".sku-props__js-size"));
        this.querySelectorAll(".sku-props__js-size").forEach(function (element) {
          element.innerHTML = "&mdash;";
          element.closest(".sku-props__item").classList.add("sku-props--no-current");
        });

        //set active sku prop
        this.querySelectorAll(".sku-props__value").forEach(function (element) {
          element.classList.remove("sku-props__value--active");
        });
        $item.classList.add("sku-props__value--active");

        const $parentItem = $item.closest(".sku-props__item");
        $parentItem.classList.remove("sku-props--no-current");

        const $titleGroupItem = $parentItem.querySelector(".sku-props__js-size");
        if ($titleGroupItem) {
          $titleGroupItem.textContent = $item.dataset.title;
        }
      });
    }
  };
  ready(clickHandler);
})();
