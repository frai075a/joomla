<?php
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

class plgContentttc_script_content_viewer extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplicationInterface
     * @since  4.0.0
     */
    protected $app;

    /**
     * Database Driver Instance
     *
     * @var    DatabaseDriver
     * @since  4.0.0
     */
    protected $db;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        if (!preg_match_all('/{ttc_script_content#/', $row->text)) {
            return true;
        }

        $db              = $this->db;
        $takenextelement = false;
        $reqscriptname   = preg_split('/{|}|#/', $row->text, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($reqscriptname as $erg) {
            if ($takenextelement) {
                $html  = '';
                $query = $db->getQuery(true);

                $query->select($db->quoteName('codescript'))
                    ->from($db->quoteName('#__ttc_script_content'))
                    ->where($db->quoteName('scriptname') . ' = :scriptname')
                    ->bind(':scriptname', $erg, ParameterType::STRING);

                $db->setQuery($query);

                try {
                    $rows = $db->loadObjectList();
                } catch (RuntimeException $e) {
                    $this->app->enqueueMessage($e->getMessage(), 'error');
                    return false;
                }

                foreach ($rows as $myrow) {
                    if (!preg_match('/src=["\']([^"\']+)["\']/', $myrow->codescript, $matches)) {
                        // Kein src gefunden – Originalcode direkt ausgeben
                        $html = $myrow->codescript;
                        continue;
                    }

                    // Doppelt-encodiertes &amp;amp; → & normalisieren
                    $src_url   = htmlspecialchars_decode(htmlspecialchars_decode($matches[1]));
                    $unique_id = 'ttc_map_' . md5($src_url . $erg);

                    // Wrapper mit position:relative als Anker für das absolute Overlay
                    $html .= '<div id="' . $unique_id . '" style="position:relative; height:50vh;">' . "\n";

                    // Iframe – src wird erst nach Zustimmung per JS gesetzt
                    $html .= '  <iframe'
                           . ' data-src="' . $src_url . '"'
                           . ' width="100%"'
                           . ' height="100%"'
                           . ' style="border:0; display:block;"'
                           . ' allowfullscreen=""'
                           . ' loading="lazy"'
                           . ' referrerpolicy="no-referrer-when-downgrade">'
                           . '</iframe>' . "\n";

                    // Consent-Overlay
                    $html .= '  <div class="ttc-map-overlay" style="'
                           . 'position:absolute; top:0; left:0; width:100%; height:100%;'
                           . 'box-sizing:border-box; background:#ddd;'
                           . 'display:flex; flex-direction:column;'
                           . 'align-items:center; justify-content:center;'
                           . 'padding:30px; text-align:center;">' . "\n";
                    $html .= '    <p style="margin:0 0 20px; max-width:480px; color:#333; font-size:14px;">'
                           . 'Zum Aktivieren der eingebetteten Karte bitte auf den Button klicken.<br>'
                           . 'Durch das Aktivieren werden Daten an Google &uuml;bermittelt.'
                           . '</p>' . "\n";
                    $html .= '    <button'
                           . ' type="button"'
                           . ' data-map-id="' . $unique_id . '"'
                           . ' class="ttc-map-consent-btn"'
                           . ' style="padding:10px 24px; background:#004280; color:#fff;'
                           . ' border:none; border-radius:3px; cursor:pointer; font-size:14px;">'
                           . 'Karte anzeigen'
                           . '</button>' . "\n";
                    $html .= '  </div>' . "\n";
                    $html .= '</div>' . "\n";

                    // Vanilla-JS – kein jQuery, kein href="#!"
                    $html .= '<script>' . "\n";
                    $html .= '(function () {' . "\n";
                    $html .= '  function ttcInitMap(id) {' . "\n";
                    $html .= '    var wrap = document.getElementById(id);' . "\n";
                    $html .= '    if (!wrap) return;' . "\n";
                    $html .= '    var btn = wrap.querySelector(".ttc-map-consent-btn");' . "\n";
                    $html .= '    if (!btn) return;' . "\n";
                    $html .= '    btn.addEventListener("click", function () {' . "\n";
                    $html .= '      var iframe = wrap.querySelector("iframe");' . "\n";
                    $html .= '      iframe.src = iframe.getAttribute("data-src");' . "\n";
                    $html .= '      wrap.querySelector(".ttc-map-overlay").style.display = "none";' . "\n";
                    $html .= '    });' . "\n";
                    $html .= '  }' . "\n";
                    $html .= '  if (document.readyState === "loading") {' . "\n";
                    $html .= '    document.addEventListener("DOMContentLoaded", function () {' . "\n";
                    $html .= '      ttcInitMap("' . $unique_id . '");' . "\n";
                    $html .= '    });' . "\n";
                    $html .= '  } else {' . "\n";
                    $html .= '    ttcInitMap("' . $unique_id . '");' . "\n";
                    $html .= '  }' . "\n";
                    $html .= '})();' . "\n";
                    $html .= '</script>' . "\n";
                }

                $regex     = '/{ttc_script_content#' . $erg . '}/s';
                $row->text = preg_replace($regex, $html, $row->text);
            }

            if ($erg === 'ttc_script_content') {
                // Das nächste Element enthält den gesuchten Skriptnamen
                $takenextelement = true;
            } else {
                $takenextelement = false;
            }
        }

        return true;
    }
}