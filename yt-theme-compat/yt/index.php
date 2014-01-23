<?php

    if( bp_yt_is_settings() )
        bp_yt_load_template( 'yt/settings.php');
    else
        bp_yt_load_template ( 'yt/members.php' );
