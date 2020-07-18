<?php
?>
<h1><?php echo $title;?></h1>
<table border = "1">
    <thead>
        <?php for($a = 0; $a<count($header); $a++):?>
            <th><?php echo $header[$a];?></th>
        <?php endfor;?>
    </thead>
    <?php for($a = 0; $a<count($data);$a++):?>
    <tr>
        <?php for($b = 0; $b<count($access_key); $b++):?>
        <td><?php echo $data[$a][$access_key[$b]];?></td>
        <?php endfor;?>
    </tr>
    <?php endfor;?>
</table>