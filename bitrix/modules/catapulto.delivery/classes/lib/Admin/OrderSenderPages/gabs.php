<?php
namespace Ipol\Catapulto\Admin;
?>
<script type="text/javascript">
    <?=self::$MODULE_LBL?>export.addPage('gabs', {
        mode: false,

        edit: function (which) {
            this.clear();
            this.mode = which;
            $('#<?=self::$MODULE_LBL?>' + which + 'Editor').css('display', 'block');
            $('#<?=self::$MODULE_LBL?>' + which + 'Place').css('display', 'none');
            if (this.mode === 'gabs') {
                $('#<?=self::$MODULE_LBL?>length_edit').val($('#<?=self::$MODULE_LBL?>length').val());
                $('#<?=self::$MODULE_LBL?>width_edit').val($('#<?=self::$MODULE_LBL?>width').val());
                $('#<?=self::$MODULE_LBL?>height_edit').val($('#<?=self::$MODULE_LBL?>height').val());
            } else
                $('#<?=self::$MODULE_LBL?>' + this.mode + '_edit').val($('#<?=self::$MODULE_LBL?>' + this.mode).val());
        },

        clear: function () {
            this.mode = false;
            $('#<?=self::$MODULE_LBL?>gabsEditor').css('display', '');
            $('#<?=self::$MODULE_LBL?>weightEditor').css('display', '');
            $('#<?=self::$MODULE_LBL?>gabsPlace').css('display', '');
            $('#<?=self::$MODULE_LBL?>weightPlace').css('display', '');
        },

        apply: function () {
            if (this.mode === 'gabs') {
                var l = parseFloat($('#<?=self::$MODULE_LBL?>length_edit').val().replace(',', '.'));
                var w = parseFloat($('#<?=self::$MODULE_LBL?>width_edit').val().replace(',', '.'));
                var h = parseFloat($('#<?=self::$MODULE_LBL?>height_edit').val().replace(',', '.'));
                if (l && w && h) {
                    $('#<?=self::$MODULE_LBL?>length').val(l);
                    $('#<?=self::$MODULE_LBL?>width').val(w);
                    $('#<?=self::$MODULE_LBL?>height').val(h);
                    $('#<?=self::$MODULE_LBL?>gabsLabel').html(l + " X " + w + " X " + h);
                }
            }
            else {
                var value = parseFloat($('#<?=self::$MODULE_LBL?>' + this.mode + '_edit').val().replace(',', '.'));
                if (value) {
                    $('#<?=self::$MODULE_LBL?>' + this.mode).val(value);
                    $('#<?=self::$MODULE_LBL?>' + this.mode + 'Label').html(value);
                }
            }
            this.self.getPage('main').calculate();
            this.clear();
        }
    });

</script>