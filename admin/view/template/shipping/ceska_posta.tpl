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
              <?php if ($doporuceny_balicek) { ?>
              <input type="checkbox" name="ceska_posta_doporuceny_balicek" value="1" checked="checked" />
              <?php echo $entry_doporuceny_balicek; ?>
              <?php } else { ?>
              <input type="checkbox" name="ceska_posta_doporuceny_balicek" value="1" />
              <?php echo $entry_doporuceny_balicek; ?>
              <?php } ?>
            </div>
            <div class="odd">
              <?php if ($cenny_balik) { ?>
              <input type="checkbox" name="ceska_posta_cenny_balik" value="1" checked="checked" />
              <?php echo $entry_cenny_balik; ?>
              <?php } else { ?>
              <input type="checkbox" name="ceska_posta_cenny_balik" value="1" />
              <?php echo $entry_cenny_balik; ?>
              <?php } ?>
            </div>
            <div class="even">
              <?php if ($ems) { ?>
              <input type="checkbox" name="ceska_posta_ems" value="1" checked="checked" />
              <?php echo $entry_ems; ?>
              <?php } else { ?>
              <input type="checkbox" name="ceska_posta_ems" value="1" />
              <?php echo $entry_ems; ?>
              <?php } ?>
            </div>
            <div class="odd">
              <?php if ($obchodni_balik) { ?>
              <input type="checkbox" name="ceska_posta_obchodni_balik" value="1" checked="checked" />
              <?php echo $entry_obchodni_balik; ?>
              <?php } else { ?>
              <input type="checkbox" name="ceska_posta_obchodni_balik" value="1" />
              <?php echo $entry_obchodni_balik; ?>
              <?php } ?>
            </div>
          </td>
        </tr>
        <tr>
          <td><?php echo $entry_doporuceny_balicek; ?></td>
          <td><textarea name="ceska_posta_doporuceny_balicek_ceny" cols="40" rows="5"><?php echo $doporuceny_balicek_ceny; ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $entry_cenny_balik; ?></td>
          <td><textarea name="ceska_posta_cenny_balik_ceny" cols="40" rows="5"><?php echo $cenny_balik_ceny; ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $entry_ems; ?></td>
          <td><textarea name="ceska_posta_ems_ceny" cols="40" rows="5"><?php echo $ems_ceny; ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $entry_obchodni_balik; ?></td>
          <td><textarea name="ceska_posta_obchodni_balik_ceny" cols="40" rows="5"><?php echo $obchodni_balik_ceny; ?></textarea></td>
        </tr>
        <tr>
          <td><?php echo $entry_tax; ?></td>
          <td><select name="ceska_posta_tax_class_id">
              <option value="0"><?php echo $text_none; ?></option>
              <?php foreach ($tax_classes as $tax_class) { ?>
              <?php if ($tax_class['tax_class_id'] == $ceska_posta_tax_class_id) { ?>
              <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="ceska_posta_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $ceska_posta_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="ceska_posta_status">
              <?php if ($ceska_posta_status) { ?>
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
          <td><input type="text" name="ceska_posta_sort_order" value="<?php echo $ceska_posta_sort_order; ?>" size="1" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>
</div>
<?php echo $footer; ?>
