<?php

/*
 * plugin name: SLENC frontend
 * description: SLENC interface for users
 * author: Nazeefa
 */

//DB connection
$path = preg_replace('/wp-content.*$/','',__DIR__);
include($path.'wp-load.php');

function SLENC(){

    ?>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>

    </head>
    <body>
    <form action="" method="post" enctype='multipart/form-data' >
        <br>
        <p style="font-family: Roboto;color: black;font-size: large;font-weight: bold">Welcome to the SLENC Concordance Tool!<p/>
        <hr>
        <p style="font-family: Roboto;color: black">
            The SLENC Concordance Tool is designed to help you generate keywords-in-context (KWIC) related to a word of your choice in the Sri Lanka English Newspaper Corpus (SLENC).
            The corpus data you can access here on the Concordance Tool approximates 10-million words from both the Daily Mirror sub-corpus (DM) and the Daily News sub-corpus (DN).
            <br><br>
            Please enter a word in the search bar and the tool will generate results from the 10-million word corpus. You will see the result count on the top-left corner below the search bar.

            <br><br>
            The results generated via the tool can be downloaded as an excel file.
            <br><br>
            We strongly recommend the use of the full Sri Lanka English Newspaper Corpus data (approximately 32 million words) for research purposes.
            <br><br>
            If you’re a researcher interested in using the full Sri Lanka English Newspaper Corpus data in your work, please download the Corpus from <a href="/slenc/#slencFile">here</a>.
            <br><br>
            If you’d like to read more about SLENC, please download the manual from <a href="/resources/#slencManual">here</a>.
        </p>

        <h4>The Concordance Tool is case sensitive</h4>

        <hr>
        <label>Please enter a word of your choice</label><br>
        <input type="text" name="word">
        <br><br>
        <input type="submit" name="submit" id="submit" value="Generate keyword in context">

    </form>
    </body>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var itemcount=$("#items").val();
            $("#itemstxt").text("Frequency : " + itemcount)

        })

        function ExportToExcel2(type, fn, dl) {
            var elt = document.getElementById('dataTable');
            var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });

            return dl ?
                XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
                XLSX.writeFile(wb, fn || ('SLENC_Results.' + (type || 'xlsx')));
        }



    </script>
    </html>

    <?php

    if (isset($_POST['submit'])){
        global $wpdb;
        echo "</br>";
        echo "</br>";
        echo "<hr>";
        ?>
        <table border="1" id="dataTable">
            <tr>
                <th>Filename</th>
                <th></th>
                <th>Word</th>
                <th></th>
            </tr>
            <?php
            global $wpdb;
            $word=" ".$_POST['word']." ";
            //fake space needed
            //case insensitive

            $slenc_content = $wpdb->get_results ( "SELECT * from slenc_data");
            $GLOBALS["arr"]= array();
            $GLOBALS["counter"]= 0;

            function getOccurrencesLeadAndTrail($string,$key)
            {
                ?>
                <?php
                $pos=  strpos($string,$key);
                if(!$pos>0)
                {
                    return "No Matching Found";
                }
                else{

                    $beforeFull=  substr($string,0,$pos);

                    //before part
                    $substringb = substr($beforeFull,-50);

                    $afterfull = substr($string, $pos+  strlen($key));

                    // after part
                    $substringa = substr($afterfull,0,50);

                    $arr=array(
                        "before"=>$substringb,
                        "after"=>$substringa
                    );
                    $GLOBALS["counter"]+=1;


                    array_push($GLOBALS["arr"], $arr);
                    getOccurrencesLeadAndTrail($afterfull,$key);
                }

            }

            ?>
            <!--This is where the count should appear-->
            <h2 id="itemstxt"></h2>
            <h2 align="right">Keyword : <?php echo $word; ?></h2>
            <input type="submit" id="download" onclick="ExportToExcel2('xlsx')" value="Download Results">

            <hr>
            <?php
            foreach ($slenc_content as $print) {
                $GLOBALS["arr"]= array();
                $string=$print->content;
                $filename=$print->filename;
                getOccurrencesLeadAndTrail($string, $word);

                foreach ($GLOBALS["arr"] as $data)
                {
                    ?>
                    <tr>
                        <td><a href=<?php echo "slenc-content?article=".$print->id; ?>><?php echo $filename;?></a></td>
                        <td><?php echo $data["before"];  ?></td>
                        <td><?php echo $word  ?></td>
                        <td><?php echo $data["after"];  ?></td>
                    </tr>
                    <?php
                }
                ?>

                <?php

                }

            ?>
        </table>
        <input type="hidden" id="items" value="<?php echo $GLOBALS["counter"]; ?>"/>

        <?php

    }

    ?>

<?php
}
add_shortcode('SLENC','SLENC'); //Adding anything under this cause errors
?>