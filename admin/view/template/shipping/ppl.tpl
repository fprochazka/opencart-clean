<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
<div class="box">
  <div class="left"></div>
  <div class="right"></div>
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><?php echo $entry_sluzby; ?></td>
          <td>
            <div class="even">
              <?php if ($ppl_cz) { ?>
              <input type="checkbox" name="ppl_ppl_cz" value="1" checked="checked" />
              <?php echo $entry_ppl_cz; ?>
              <?php } else { ?>
              <input type="checkbox" name="ppl_ppl_cz" value="1" />
              <?php echo $entry_ppl_cz; ?>
              <?php } ?>
            </div>
            <div class="odd">
              <?php if ($ppl_sk) { ?>
              <input type="checkbox" name="ppl_ppl_sk" value="1" checked="checked" />
              <?php echo $entry_ppl_sk; ?>
              <?php } else { ?>
              <input type="checkbox" name="ppl_ppl_sk" value="1" />
              <?php echo $entry_ppl_sk; ?>
              <?php } ?>
            </div>
            <div class="even">
              <?php if ($ppl_de) { ?>
              <input type="checkbox" name="ppl_ppl_de" value="1" checked="checked" />
              <?php echo $entry_ppl_de; ?>
              <?php } else { ?>
              <input type="checkbox" name="ppl_ppl_de" value="1" />
              <?php echo $entry_ppl_de; ?>
              <?php } ?>
            </div>
            <div class="odd">
              <?php if ($ppl_pl) { ?>
              <input type="checkbox" name="ppl_ppl_pl" value="1" checked="checked" />
              <?php echo $entry_ppl_pl; ?>
              <?php } else { ?>
              <input type="checkbox" name="ppl_ppl_pl" value="1" />
              <?php echo $entry_ppl_pl; ?>
              <?php } ?>
            </div>
            <div class="even">
              <?php if ($dobirka) { ?>
              <input type="checkbox" name="ppl_dobirka" value="1" checked="checked" />
              <?php echo $entry_dobirka; ?>
              <?php } else { ?>
              <input type="checkbox" name="ppl_dobirka" value="1" />
              <?php echo $entry_dobirka; ?>
              <?php } ?>
            </div>
          </td>
        </tr>
        <tr>
          <td><?php echo $entry_ppl_cz; ?></td>
          <td><textarea name="ppl_ppl_cz_ceny" cols="40" rows="5"><?php echo $ppl_cz_ceny; ?></textarea></td>
        <tr>
        <tr>
          <td><?php echo $entry_ppl_sk; ?></td>
          <td><textarea name="ppl_ppl_sk_ceny" cols="40" rows="5"><?php echo $ppl_sk_ceny; ?></textarea></td>
        <tr>
        <tr>
          <td><?php echo $entry_ppl_de; ?></td>
          <td><textarea name="ppl_ppl_de_ceny" cols="40" rows="5"><?php echo $ppl_de_ceny; ?></textarea></td>
        <tr>
        <tr>
          <td><?php echo $entry_ppl_pl; ?></td>
          <td><textarea name="ppl_ppl_pl_ceny" cols="40" rows="5"><?php echo $ppl_pl_ceny; ?></textarea></td>
        <tr>
        <tr>
          <td><?php echo $entry_dobirka; ?></td>
          <td><textarea name="ppl_dobirka_ceny" cols="40" rows="5"><?php echo $dobirka_ceny; ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $entry_tax; ?></td>
          <td><select name="ppl_tax_class_id">
              <option value="0"><?php echo $text_none; ?></option>
              <?php foreach ($tax_classes as $tax_class) { ?>
              <?php if ($tax_class['tax_class_id'] == $ppl_tax_class_id) { ?>
              <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="ppl_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $ppl_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="ppl_status">
              <?php if ($ppl_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_sort_order; ?></td>
          <td><input type="text" name="ppl_sort_order" value="<?php echo $ppl_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>
