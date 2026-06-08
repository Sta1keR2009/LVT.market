					<?if(!$isIndex):?>
						<?TSolution::checkRestartBuffer();?>
					<?endif;?>
					<?IncludeTemplateLangFile(__FILE__);?>
					<?global $arTheme, $isIndex, $is404;?>
					<?if(!$isIndex):?>
							<?if($is404):?>
								</div>
							<?else:?>
									<?TSolution::get_banners_position('CONTENT_BOTTOM');?>
									</div> <?// class=right_block?>
									<?if($APPLICATION->GetProperty("MENU") != "N" && !defined("ERROR_404")):?>
										<?TSolution::ShowPageType('left_block');?>
									<?endif;?>
								</div><?// class=col-md-12 col-sm-12 col-xs-12 content-md?>
							<?endif;?>
							</div><?// class="maxwidth-theme"?>
						</div><?// class=row?>
					<?else:?>
						<?TSolution::ShowPageType('indexblocks');?>
					<?endif;?>
				</div><?// class=container?>
				<?TSolution::get_banners_position('FOOTER');?>
			</div><?// class=main?>
		</div><?// class=body?>
		<?TSolution::ShowPageType('footer');?>
		<?@include_once(str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_DIR.'include/footer/bottom_footer.php'));?>
	</body>
</html>