<?php
/* Copyright (C) 2025 ATM Consulting
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
	require 'config.php';
	dol_include_once('/core/lib/functions.lib.php');
	dol_include_once('/searcheverywhere/lib/searcheverywhere.lib.php');

	$langs->load('searcheverywhere@searcheverywhere');

	$keyword = GETPOST('keyword');
	if (empty($keyword)) $keyword=GETPOST('sall');
	if (empty($keyword)) $keyword=GETPOST('search_all');

	llxHeader('', $langs->trans('Searcheverywhere'), '', '', 0, 0);
	$head = searcheverywhere_prepare_head($keyword);
	dol_fiche_head($head, 'search', $langs->trans('Searcheverywhere'), 0, 'searcheverywhere@searcheverywhere');
?>

	<style type="text/css">
		#results {
			position:relative;
			margin-top:15px;
		}

		#results span.loading {
			padding : 20px;
			background-color: #f64f1c;
			border-radius: 10px;
			top:50px;
			left:50px;
			position:relative;
		}

		#results div.result {
			width:300px;
			float:left;
			border-color: #bbb #aaa #aaa;
			border-style: solid;
			border-width: 1px;
			box-shadow: 3px 3px 4px #ddd;
			margin: 0 10px 14px 0;
		}
		.highlight {
			font-weight: bold;
		}
	</style>

	<input type="text" name="keyword" id="keyword" value="" />
	<input type="button" name="btsearch" id="btsearch" value="<?php print $langs->trans('Search'); ?>" />
	<div id="results"></div>
	<div style="clear:both"></div>
	<script type="text/javascript">
		var url = "<?php echo dol_buildpath('/searcheverywhere/search.php?keyword=', 1) ?>";
			var TSearch = [
				'product',
				'company',
				'contact',
				<?php if (!empty($user->rights->user->user->lire)) echo "'user',"; ?>
				<?php if (isModEnabled('propal')) echo "'propal',"; ?>
			<?php if (isModEnabled('commande')) echo "'order',"; ?>
			<?php if (isModEnabled('facture')) echo "'invoice',"; ?>
			<?php if (isModEnabled('projet')) echo "'projet','task',"; ?>
			<?php if (isModEnabled('agenda')) echo "'event',"; ?>
			<?php if (isModEnabled('expedition')) echo "'expedition',"; ?>
			<?php if (isModEnabled('expeditionfournisseur')) echo "'supplier_order',"; ?>
			<?php if (isModEnabled('of')) echo "'of',"; ?>
			<?php if (isModEnabled('nomenclature')) echo "'nomenclature',"; ?>
			<?php if (isModEnabled('workstationatm')) echo "'workstation',"; ?>
			<?php if (isModEnabled('configurateur')) echo "'configurateur',"; ?>
			<?php if (isModEnabled('fournisseur')) echo "'invoice_supplier',"; ?>
		];

		$(document).ready(function() {
			$("#btsearch").click(function() {
				var keyword = $("#keyword").val();

				$('#results').html("<span class=\"loading\"><?php echo $langs->trans('Loading'); ?>...</span>");
				$('a#search').attr('href', url+keyword);

				for(x in TSearch) {
					$.ajax({
						url : "./script/interface.php"
						,data :{
							get:'search'
							,type:TSearch[x]
							,keyword : keyword
						}

					}).done(function(data) {
						$('#results span.loading').remove();

						$div = $('<div class="result" />');
						$div.append(data);
						$('#results').append($div);

						// Re-init standard tooltips on dynamically loaded elements
						$div.find(".classfortooltip").tooltip({
							tooltipClass: "mytooltip",
							show: { collision: "flipfit", effect: "toggle", delay: 50, duration: 20 },
							hide: { delay: 250, duration: 20 },
							content: function() { return $(this).prop("title"); }
						});

						// Re-init AJAX tooltips on dynamically loaded elements (mirrors lib_foot.js.php)
						var ajaxTargets = $div.find(".classforajaxtooltip");
						if (ajaxTargets.length) {
							var dialogElem = jQuery("#dialogforpopup");
							var csrfToken = jQuery("meta[name=anti-csrf-currenttoken]").attr("content");

							ajaxTargets.tooltip({
								tooltipClass: "mytooltip",
								show: { collision: "flipfit", effect: "toggle", delay: 0, duration: 20 },
								hide: { delay: 250, duration: 20 }
							});

							ajaxTargets.on("mouseover", function(event) {
								event.stopImmediatePropagation();
								clearTimeout(dialogElem.data("openTimeoutId"));
								var params = JSON.parse($(this).attr("data-params"));
								params.token = csrfToken;
								var elemfortooltip = $(this);
								dialogElem.data("openTimeoutId", setTimeout(function() {
									ajaxTargets.tooltip("close");
									$.ajax({
										url: "<?php echo DOL_URL_ROOT; ?>/core/ajax/ajaxtooltip.php",
										type: "post",
										async: true,
										data: params,
										success: function(response) {
											if (elemfortooltip.is(":hover")) {
												elemfortooltip.tooltip("option", "content", response);
												elemfortooltip.tooltip("open");
											}
										}
									});
								}, 100));
							});

							ajaxTargets.on("mouseout", function(event) {
								event.stopImmediatePropagation();
								clearTimeout(dialogElem.data("openTimeoutId"));
								ajaxTargets.tooltip("close");
							});
						}
					})
				}
			});
			<?php
			if ($keyword!='') {
				?>
					$("#keyword").val("<?php echo $keyword; ?>");
					$("#btsearch").click();
					<?php
			}
			?>
		});
	</script>

	<?php
	llxFooter();
