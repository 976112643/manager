<extend name="Public/body" />
<block name="body">
<div class="box box-body">
	<!-- form start -->
	<form action="" method="post" class="js_form">
		<input type='hidden' name='id' value='<?=$info["id"]?>' /> <label
			class="form-group col-xs-12">
			<div class="clearfix row">
				<div class="control-label col-xs-1 align_r">记录名称</div>
				<div class="col-xs-9">
					<?=$info['item']?>
				</div>
			</div>
		</label>
		<div class="checklist">
			<div class="js_options" data-value="28">
				<label class="form-group col-xs-12">
					<div class="clearfix row">
						<div class="control-label col-xs-1 align_r">
							<span class="required" style="font-size: 14px;">*</span>内容
						</div>
						<div class="col-xs-9">
                            <pre></pre>
							<?= \Common\Help\BaseHelp::show($info['content']) ?>
						</div>
					</div>
				</label>
			</div>
		</div>
		<label class="form-group col-xs-12">
			<div class="clearfix row">
				<div class="control-label col-xs-1 align_r">添加时间</div>
				<div class="col-xs-9">
					<?=$info['addtime']?>
				</div>
			</div>
		</label>
		<div class="form-btn clearfix">
			<a href="<?=U('index')?>" class="pull-left btn btn-default">取消并返回</a>
			<div class="js_validTips pull-left"
				style="height: 34px; line-height: 34px;">
				<span class="js_tipContent Validform_checktip"></span>
			</div>
		</div>
	</form>
</div>
</block>