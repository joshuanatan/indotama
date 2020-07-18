<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=list_".$no_pr.".xls");
?>
<h1>DATA JAS NO PR <?php echo $no_pr;?></h1>
<table border = "1">
    <thead>
        <?php for($a = 0; $a<count($header); $a++):?>
            <th><?php echo $header[$a];?></th>
        <?php endfor;?>
    </thead>
    <?php for($counter = 0; $counter<count($jas);$counter++):?>
    <tr>
        <td><?php echo $jas[$counter]["id_submission_ukuran"];?></td>
        <td><?php echo $jas[$counter]["nama_user"];?></td>
        <td><?php echo $jas[$counter]["nim_mahasiswa"];?></td>
        <td><?php echo $jas[$counter]["nohp_final"];?></td>
        <td><?php echo $text_status;?></td>
        <td><?php echo $jas[$counter]["email_user"];?></td>
        <td><?php echo $jas[$counter]["tinggi_mahasiswa"];?></td>
        <td><?php echo $jas[$counter]["berat_mahasiswa"];?></td>
        <td><?php echo $jas[$counter]["jk_mahasiswa"];?></td>
        <th><?php echo $jas[$counter]["jas_lebar_bahu"];?></th>
        <th><?php echo $jas[$counter]["jas_lebar_tangan"];?></th>
        <th><?php echo $jas[$counter]["jas_lingkar_dada"];?></th>
        <th><?php echo $jas[$counter]["jas_lingkar_ketiak"];?></th>
        <th><?php echo $jas[$counter]["jas_lingkar_perut"];?></th>
        <th><?php echo $jas[$counter]["jas_lingkar_pinggul"];?></th>
        <th><?php echo $jas[$counter]["jas_bahu_pinggul"];?></th>
        <th><?php echo $jas[$counter]["jas_bahu_pergelangan"];?></th>
    </tr>
    <?php endfor;?>
</table>