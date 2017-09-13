<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-8-9
 * Time: 下午11:21
 */

return [
    'app_debug'     => true,
    'app_model'     =>"api", //default, api

    'log'           =>  'daily',
    'log_max_files' =>  30,
    'log_level'     =>  "debug", //debug , info , notice , warning , error , critical , alert , emergency
    'log_file'      =>  "yf",
    'log_channel'   =>  "LOG"
];