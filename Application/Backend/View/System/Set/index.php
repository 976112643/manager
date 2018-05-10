<extend name="Public/body"/>
<block name="body">
    <include file="Common@Plugins/editor"/>
    <form action="<?= U('index') ?>" method="post" class="operate_form">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs" data-content="tab-content">
                <?php
                $_tab = assoc_unique($_result, 'groups');
                foreach ($_tab as $k => $v) {
                    ?>
                    <li class="js_tab <?php if ($k == 0) {
                        echo 'active';
                    } ?>"><a
                                href="javascript:void(0)" data-toggle="tab">
                            <?= $v['groups'] ?>
                        </a></li>
                    <?php
                }
                ?>
            </ul>
            <div class="tab-content">
                <?php
                $index = 0;
                foreach ($_tab as $k => $v) {
                    ?>
                    <div
                            class="tab_hide js_tab_content <?php if ($k == 0) {
                                echo 'active';
                            } ?>">
                        <?php
                        foreach ($_result as $_k => $_v) {
                            if ($_v['groups'] == $v['groups']) {
                                $index++;
                                ?>
                                <div class="box-body">
                                    <div class="form-group">
                                        <div>
                                            <?= $_v['title'] ?>
                                            <?php if ($_v['description'] && $_v['type'] != 'text') { ?>
                                                <span class="Validform_checktip"> （<?= $_v['description'] ?>）
							</span>
                                            <?php } ?>
                                        </div>
                                        <input type="hidden" name="form_<?= $_v['id'] ?>[]"
                                               value="<?= $_v['type'] ?>"> <input type="hidden"
                                                                                  name="form_<?= $_v['id'] ?>[]"
                                                                                  value="<?= $_v['id'] ?>">
                                        <?php
                                        switch ($_v['type']) {
                                            case 'radio':
                                                $options = explode("\n", $_v['options']);
                                                echo '<span class="form-inline clearfix">';
                                                foreach ($options as $key => $value) {
                                                    $radio = explode(":", $value);
                                                    ?>
                                                    <label class=""> <input type="radio"
                                                                            name="form_<?= $_v['id'] ?>[]"<?php
                                                        if ($radio[0] == $_v['value']) {
                                                            echo 'checked="checked"';
                                                        } ?>
                                                                            value="<?= $radio[0] ?>"> <?= $radio[1] ?>
                                                    </label>
                                                    <?php
                                                }
                                                echo '</span>';
                                                break;
                                            case 'image':
                                                $config = array(
                                                    'table' => 'config',
                                                    'name' => 'form_' . $_v['id'] . '[]',
                                                    'index' => $index,
                                                    'val_key' => "value"
                                                );
                                                echo W("Common/Img/index", array($config, $_v['id']));
                                                break;
                                            case 'textarea':
                                                ?>
                                                <textarea class="form-control width_auto" rows="3" placeholder=""
                                                          name="form_<?= $_v['id'] ?>[]"><?= $_v['value'] ?></textarea>
                                                <?php
                                                break;
                                            case 'select':
                                                ?>
                                                <select class="form-control width_auto"
                                                        name="form_<?= $_v['id'] ?>[]">
                                                    <?php
                                                    $options = explode("\n", $_v['options']);
                                                    foreach ($options as $key => $row) {
                                                        $radio = explode(":", $value);
                                                        ?>
                                                        <option value="<?= $radio[0] ?>"<?php
                                                        if ($radio[0] == $_v['value']) {
                                                            echo "selected";
                                                        } ?>>
                                                            <?= $radio[1] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                                <?php
                                                break;
                                            case 'editor':
                                                ?>
                                                <div>
                                                    <!--百度编辑器-->
                                                    <?php $time = time() . $index; ?>
                                                    <script id="<?= 'editor_' . $time ?>" type="text/plain"
                                                            name="form_<?= $_v['id'] ?>[]"><?= htmlspecialchars_decode($_v['value']) ?></script>
                                                    <script type="text/javascript">
                                                        var config_2 = {
                                                            initialFrameWidth: "100%",
                                                            initialFrameHeight: 300,
                                                            pasteplain: true,
                                                            toolbars: [[
                                                                'fullscreen', 'bold', 'italic', 'underline', 'superscript', 'subscript', 'removeformat', 'insertorderedlist', 'insertunorderedlist', 'customstyle', 'paragraph', 'fontsize', 'indent', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', 'link', 'unlink', 'anchor', 'imagenone', 'imageleft', 'imageright', 'imagecenter', 'simpleupload', 'insertimage', 'insertvideo', 'map', 'gmap', 'insertframe', 'horizontal'
                                                            ]]
                                                        };
                                                        //合并配置文件，config为默认配置，config_2为自定义的配置
                                                        var new_config = array_merge({}, [config, config_2]);
                                                        //说明：editor_obj_id的值必须和编辑器ID保持一致
                                                        var editor_<?=$time?>= UE.getEditor('editor_<?=$time?>', new_config);

                                                        $(document).on("click", ".js_tab", function () {
                                                            editor_<?=$time?>.enableAutoHeight();
                                                        })
                                                    </script>
                                                </div>
                                                <?php
                                                break;
                                            default:
                                                ?>
                                                <input type="text" name="form_<?= $_v['id'] ?>[]"
                                                       value="<?= $_v['value'] ?>" class="form-control width_auto"
                                                       placeholder="">
                                                <?php

                                                break;
                                        }
                                        ?>
                                        <?php if ($_v['description'] && $_v['type'] == 'text') { ?>
                                            <span class="Validform_checktip"> <?= $_v['description'] ?>
						</span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
                <div class="box-body">
                    <input type="submit" class="btn btn-primary ajax-post"
                           target-form="operate_form" value="提交">
                    <button type="button" class="btn btn-default js_cancel">取消并返回</button>
                </div>
            </div>
            <!-- /.tab-pane -->
        </div>
    </form>
</block>