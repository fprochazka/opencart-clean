<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($success) { ?><div class="success"><?php echo htmlspecialchars($success); ?></div><?php } ?>
  <?php if ($error) { ?><div class="warning"><?php echo htmlspecialchars($error); ?></div><?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/feed.png" alt="" /> <?php echo $heading_title; ?></h1>
    </div>
    <div class="content">
        <table class="form">
		  <?php foreach ($imports as $import): ?>
          <tr><td>
			  <a href="<?php echo $import['action'] ?>" data-importfile-progress="<?php echo $import['importFile'] ?>" class="button import-products"><?php echo htmlspecialchars($action_load); ?></a>
			  <?php echo htmlspecialchars($import['title']); ?>
		  </td></tr>
	      <?php endforeach; ?>
          <?php if ($changelog) { foreach ($changelog as $change): ?>
              <tr><td><div class="<?php echo htmlspecialchars($change->type) ?>">
                <?php echo htmlspecialchars($change->message); ?>
              </div></td></tr>
          <?php endforeach; } ?>
        </table>
    </div>
  </div>
</div>
<?php echo $footer; ?>


<script>
	$('.content').on('click', '.import-products', function (e) {
		e.preventDefault();

		var link = $(this);
		var progress = {
			percents : 1,
			set : function (done) {
				if (!this.loader) {
					this.loader = $('<img />', {'src':'view/image/loading.gif'});
					link.parent().append(this.loader);
				}

				if (!this.container) {
					link.parent().css({'position':'relative'});
					this.container = $('<div />', {'class':'import-progress'})
					link.parent().append(this.container);
				}

				if (done < 1) {
					return;
				}

				this.percents = done;
				this.container.css({
					'width' : done.toString() + '%'
				});
			},
			check : function () {
				var me = this;

				this.checker = $.ajax({
					url: link.data('importfile-progress'),
					data : {'ms' :(new Date()).getTime()},
					dataType: 'json',
					timeout:5,
					complete: function (jqXHR, textStatus) {
						if (jqXHR.status) {
							var data = jQuery.parseJSON(jqXHR.responseText);
							if (data && data.percents) {
								me.set(data.percents);
							}
						}

						me.waiter = setTimeout(function () {
							me.check();
						}, 1000);
					},
					cache:false
				});
			},
			done : function () {
				if (this.loader) {
					this.loader.remove();
					this.container.fadeOut('slow');
				}

				if (this.waiter) {
					clearTimeout(this.waiter);
					this.waiter = false;
				}

				if (this.checker) {
					this.checker.abort();
				}

				this.set(100);
			}
		};

		progress.set(1);
		var jqxhr = $.getJSON(link.attr('href'), function (data) {
			progress.done();

			if (data.success) {
				alert('Import v pořádku.');

			} else {
				alert('Chyba importu!');
			}
		});
		jqxhr.error(function () {
			progress.done();
			alert('Chyba importu!');
		});

		progress.check();
	});
</script>


<style>
.import-progress {
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	margin: 0;
	padding: 0;
	width: 100%;
	min-height: 100%;
	background-color: #8ce008;
	z-index: 1;
	/* These three lines are for transparency in all browsers. */
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
	filter: alpha(opacity = 50);
	opacity: .5;
}
</style>
