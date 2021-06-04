<?php
    include_once("pgs/class.radiomodule.php");
    $i = 0;
    foreach($RadioModules as $radioModule)
    {
        $id = "radio-module-activity-" . ++$i;
        $radioModuleConf = new RadioModule($radioModule["mmdvm.ini"]);
        $radioModuleConf->init();
?>
        <div class="row justify-content-md-center">
            <table class="table table-hover table-sm table-responsive-md" id="<?php echo $id ?>">
                <thead class="thead-light">
                    <tr>
                        <th colspan="5" scope="col"><?php echo $radioModuleConf->getCallsign(); ?></th>
                    </tr>
                    <tr>
                        <th scope="col">Time</th>
                        <th scope="col">Mode</th>
                        <th scope="col">Call</th>
                        <th scope="col">Target</th>
                        <th scope="col">Source</th>
                    </tr>
                </thead>
            </table>
        </div>
<?php
    }
?>