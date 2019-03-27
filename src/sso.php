<?php
namespace MiniOrange;

// include_once 'autoload.php';
use Illuminate\Http\Request;
use MiniOrange\Classes\Actions\ProcessResponseAction;
use MiniOrange\Classes\Actions\ProcessUserAction;
use MiniOrange\Classes\Actions\ReadResponseAction;
use MiniOrange\Classes\Actions\TestResultActions;
use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Messages;
use MiniOrange\Helper\Utilities;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Classes\Actions\AuthFacadeController;
use MiniOrange\Helper\Lib\AESEncryption;

final class SSO
{

    public function __construct()
    {
        $pluginSettings = PluginSettings::getPluginSettings();
        if (array_key_exists('SAMLResponse', $_REQUEST) && ! empty($_REQUEST['SAMLResponse'])) {
            try {
                
                $relayStateUrl = array_key_exists('RelayState', $_REQUEST) ? $_REQUEST['RelayState'] : '/';
                $samlResponseObj = ReadResponseAction::execute(); // read the samlResponse from IDP
                $responseAction = new ProcessResponseAction($samlResponseObj);
                $responseAction->execute();
                $ssoemail = current(current($samlResponseObj->getAssertions())->getNameId());
                $attrs = current($samlResponseObj->getAssertions())->getAttributes();
                $attrs['NameID'] = array(
                    "0" => $ssoemail
                );
                $sessionIndex = current($samlResponseObj->getAssertions())->getSessionIndex();
                $custom_attribute_mapping = $pluginSettings->getCustomAttributeMapping();
                
                if (strcasecmp($relayStateUrl, Constants::TEST_RELAYSTATE) == 0) {
                    (new TestResultActions($attrs))->execute(); // show test results
                } else {
                    (new ProcessUserAction($attrs, $relayStateUrl, $sessionIndex))->execute(); // process user action

                    // Use attributes $attrs
                    // print_r($attrs);

                    session_id('attributes');
                    session_start();
                    $_SESSION['email'] = $attrs[$pluginSettings->getSamlAmEmail()];

                    $_SESSION['username'] = $attrs[$pluginSettings->getSamlAmUsername()];
                    
                    if (is_array($custom_attribute_mapping) && ! empty($custom_attribute_mapping))
                        foreach ($custom_attribute_mapping as $key => $value) {
                            if (array_key_exists($value, $attrs))
                                $_SESSION[$key] = $attrs[$value];
                        }
                    // var_dump($attrs['NameID'][0]);exit;
                    //var_dump($attrs['']);exit;
                    $encrypted_mail = AESEncryption::encrypt_data($_SESSION['email'][0], "secret");
                    $encrypted_name = AESEncryption::encrypt_data($_SESSION['username'][0], "secret");
                    header('Location: sign?email=' . $encrypted_mail.'&name='.$encrypted_name);exit;
                    // Redirect to application url
                    /*$applicationUrl = $pluginSettings->getApplicationUrl();

                   if (! empty($applicationUrl)) {
                        header('Location: ' . $applicationUrl);
                        exit();
                    } else {
                        echo '<html>
                        <body>You have been logged in!<br/>
                        If you want to redirect to a different URL after logging in, configure the Application url in Step 5 of <b>How to Setup?</b> tab of the connector.
                        </body>
                        </html>';
                        exit();
                    }*/
                }
            } catch (\Exception $e) {
                if (strcasecmp($relayStateUrl, Constants::TEST_RELAYSTATE) === 0)
                    (new TestResultActions(array(), $e))->execute();
                else
                    Utilities::showErrorMessage($e->getMessage());
            }
        } else {
            Utilities::showErrorMessage(Messages::MISSING_SAML_RESPONSE);
        }
    }
}
new SSO();