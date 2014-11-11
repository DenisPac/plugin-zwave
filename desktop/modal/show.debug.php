<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


if (!isConnect('admin')) {
    throw new Exception('401 Unauthorized');
}
echo '<div class="alert alert-warning">{{Attention le lancement en mode debug est très consommateur en ressources et en log, pensez bien à relancer le démon une fois l\'analyse terminée}}</div>';
?>
<div id='div_zwaveShowDebug' style="display: none;"></div>
<pre id='pre_zwavelog' style='overflow: auto; height: 85%;with:90%;'></pre>


<script>
    $.ajax({
        type: 'POST',
        url: 'plugins/zwave/core/ajax/zwave.ajax.php',
        data: {
            action: 'launchInDebug',
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error, $('#div_zwaveShowDebug'));
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_zwaveShowDebug').showAlert({message: data.result, level: 'danger'});
                return;
            }
            getDebugLog(1);
        }
    });

    function getDebugLog(_autoUpdate) {
        $.ajax({
            type: 'POST',
            url: 'core/ajax/log.ajax.php',
            data: {
                action: 'get',
                logfile: 'zwavecmd',
            },
            dataType: 'json',
            global: false,
            error: function (request, status, error) {
                setTimeout(function () {
                    getJeedomLog(_autoUpdate, 'zwavecmd')
                }, 1000);
            },
            success: function (data) {
                if (data.state != 'ok') {
                    $('#div_zwaveShowDebug').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                var log = '';
                var regex = /<br\s*[\/]?>/gi;
                for (var i in data.result.reverse()) {
                    log += data.result[i][2].replace(regex, "\n");
                }
                $('#pre_zwavelog').text(log);
                $('#pre_zwavelog').scrollTop($('#pre_zwavelog').height() + 200000);
                if (!$('#pre_zwavelog').is(':visible')) {
                    _autoUpdate = 0;
                }

                if (init(_autoUpdate, 0) == 1) {
                    setTimeout(function () {
                        getDebugLog(_autoUpdate)
                    }, 1000);
                }
            }
        });
    }

</script>