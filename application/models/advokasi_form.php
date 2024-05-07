<?php
$nomor = isset($detail) ? $detail->nomor_surat : '' ;
$tanggal = isset($detail) ? $detail->tanggal : date('Y-m-d') ;
$lembaga = isset($detail) ? $detail->lembaga : '' ;
$alamat = isset($detail) ? $detail->alamat : '' ;
$paket = isset($detail) ? $detail->kode_rup : '' ;
$materi = isset($detail) ? $detail->materi : '' ;
$tahap = isset($detail) ? $detail->tahap : '' ;
$keterangan = isset($detail) ? $detail->keterangan : '' ;
?>

<div class="content-wrapper">
 <!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			Advokasi
			<small>Control Panel</small>
		</h1>
		<ol class="breadcrumb">
			<li><a href="<?php echo base_url('admin') ?>"><i class="fa fa-dashboard"></i> Admin</a></li>
      <li>Advokasi</li>
			<li class="active">Input</li>
		</ol>
	</section>

	<section class="content">

<div class="row">
  <div class="col-xs-12">

    <div class="box box-primary">
      <div class="box-header">
        <h3 class="box-title">Edit Advokasi</h3>
      </div>
      <!-- /.box-header -->
      <div class="box-body">

        <form class="form-horizontal" method="post">

         <?php echo validation_errors(); ?>

         <div class="form-group">
           <label for="inputEmail3" class="col-sm-2 control-label">No. Surat</label>
           <div class="col-sm-6">
             <input type="text" class="form-control" name="nomor" value="<?php echo $nomor ?>">
           </div>
         </div>
         <div class="form-group">
           <label for="inputEmail3" class="col-sm-2 control-label">Tanggal</label>
           <div class="col-sm-4">
             <input type="text" class="form-control" name="tanggal" value="<?php echo $tanggal ?>">
           </div>
         </div>
         <div class="form-group">
          <label for="inputEmail3" class="col-sm-2 control-label">Lembaga</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" name="lembaga" value="<?php echo $lembaga ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="inputEmail3" class="col-sm-2 control-label">Alamat</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" name="alamat" value="<?php echo $alamat ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="inputPassword3" class="col-sm-2 control-label">Paket</label>
          <div class="col-sm-8">
            <?php
						$field = array();
						foreach ($paket_list as $value) {
							$field[$value->kode_rup] = $value->nama_paket;
						}
            echo form_dropdown('paket', $field, $paket, 'class="form-control"');
            ?>
          </div>

        </div>
        <div class="form-group">
          <label for="inputPassword3" class="col-sm-2 control-label">Materi</label>
          <div class="col-sm-6">
            <textarea name="materi" class="form-control" rows="8" cols="80"><?php echo $materi ?></textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPassword3" class="col-sm-2 control-label">Tahap</label>
          <div class="col-sm-6">
            <textarea name="tahap" class="form-control" rows="8" cols="80"><?php echo $tahap ?></textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPassword3" class="col-sm-2 control-label">Keterangan</label>
          <div class="col-sm-6">
            <textarea name="keterangan" class="form-control" rows="8" cols="80"><?php echo $keterangan ?></textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="inputPassword3" class="col-sm-2 control-label"></label>
          <div class="col-sm-10">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
          </div>
        </div>

        </form>

      </div>
      <!-- /.box-body -->
    </div>
    <!-- /.box -->
  </div>
  <!-- /.col -->
</div>

</section>

</div>
