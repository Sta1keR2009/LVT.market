<?//$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/page-title-5.css');?>
<div class="page-top-info">
	<?$APPLICATION->ShowViewContent('side-over-title')?>
	<div class="page-top-wrapper page-top-wrapper--white">
		<section class="page-top maxwidth-theme <?TSolution::ShowPageProps('TITLE_CLASS');?>">	
			<div class="cowl">
				<?$APPLICATION->ShowViewContent('cowl_buttons')?>
				<div id="navigation">
					<?$APPLICATION->IncludeComponent(
						"bitrix:breadcrumb", 
						"main", 
						array(
							"START_FROM" => "0",
							"PATH" => "",
							"SITE_ID" => SITE_ID,
							"COMPONENT_TEMPLATE" => "main"
						),
						false
					);?>
				</div>
			</div>
			<!--h1_content-->
			<div class="topic">
				<div class="topic__inner">
					<div class="topic__heading">
						<h1 id="pagetitle" class="switcher-title"><?$APPLICATION->ShowTitle(false)?></h1>
						<?$APPLICATION->ShowViewContent('more_text_title');?>
					</div>
				</div>
			</div>
			<!--/h1_content-->
		</section>
	</div>
</div>