<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use App\Events\Module\PostRegistrationEvent;
use Event;

/**
 * Class FrontendController
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
       $this->signatureRequestFromTemplate();
    }

	//https://github.com/docusign/devcenter-quickstarts
    public function signatureRequestFromTemplate()
    {
    	$path = public_path();
        $username = "marko@crowdfundup.com";
        $password = "tvornica456";
        $integrator_key = "abd75bd1-200e-490b-b0df-72e1e7859c2c";     

        // change to production before going live
        //https://www.docusign.net/restapi
        //https://www.docusign.com/p/RESTAPIGuide/Content/GettingStarted/REST%20API%20Version.htm
        $host = "https://demo.docusign.net/restapi";

         // create a new DocuSign configuration and assign host and header(s)
	    $config = new \DocuSign\eSign\Configuration();
	    $config->setHost($host);
	    $config->setSSLVerification(false);
	    $config->addDefaultHeader("X-DocuSign-Authentication", "{\"Username\":\"" . $username . "\",\"Password\":\"" . $password . "\",\"IntegratorKey\":\"" . $integrator_key . "\"}");



	    try
	    {
		    /////////////////////////////////////////////////////////////////////////
		    // STEP 1:  Login() API
		    /////////////////////////////////////////////////////////////////////////
		    // instantiate a new docusign api client
		    $apiClient = new \DocuSign\eSign\ApiClient($config);
		    // we will first make the Login() call which exists in the AuthenticationApi...
		    $authenticationApi = new \DocuSign\eSign\Api\AuthenticationApi($apiClient);
		    // optional login parameters
		    $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
		    // call the login() API
		    $loginInformation = $authenticationApi->login($options);
		    // parse the login results
		    if(isset($loginInformation) && count($loginInformation) > 0)
		    {
		        // note: defaulting to first account found, user might be a 
		        // member of multiple accounts
		        $loginAccount = $loginInformation->getLoginAccounts()[0];
		        $baseUrl=$loginAccount->getBaseUrl();
		        if(isset($loginInformation))
		        {
		            $accountId = $loginAccount->getAccountId();
		            if(!empty($accountId))
		            {
		               // echo "Account ID = $accountId\n";
		            }
		        }
		    }

			$testConfig=new \DocuSign\eSign\ApiClient($config);
		    $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($testConfig);
			$docsList = $envelopeApi->listDocuments($accountId, "acdf93aa-f1f1-4ba0-a6f6-8883c6d20251");
			//var_dump($docsList->getUrl()); 
			//$this->assertNotEmpty($docsList);
			//$this->assertNotEmpty($docsList->getEnvelopeId());
			$docCount = count($docsList->getEnvelopeDocuments()); 
			if (intval($docCount) > 0)
			{
				foreach($docsList->getEnvelopeDocuments() as $document)
				{
					print_r($document);
					//$file = $envelopeApi->getDocument($testConfig->getAccountId(), $testConfig->getEnvelopeId(), $document->getDocumentId());
					//$this->assertNotEmpty($file);
				}
			}

		return;

		    /////////////////////////////////////////////////////////////////////////
		    // STEP 2:  Create & Send Envelope with Embedded Recipient
		    /////////////////////////////////////////////////////////////////////////
		    // set recipient information
		    $email="dotri_do@yahoo.com";
		    $recipientName = "Dario";
		    $recipientEmail = $email;
		    // configure the document we want signed
		    $documentFileName = "$path/doc.pdf";
		    $documentName = "MojDoc.pdf";
		    // instantiate a new envelopeApi object
		    $envelopeApi = new \DocuSign\eSign\Api\EnvelopesApi($apiClient);
		    // Add a document to the envelope
		    $document = new \DocuSign\eSign\Model\Document();
		    $document->setDocumentBase64(base64_encode(file_get_contents($documentFileName)));
		    $document->setName($documentName);
		    $document->setDocumentId("1");
		    // Create a |SignHere| tab somewhere on the document for the recipient to sign
		    $signHere = new \DocuSign\eSign\Model\SignHere();
		    $signHere->setXPosition("60");
		    $signHere->setYPosition("600");
		    $signHere->setDocumentId("1");
		    $signHere->setPageNumber("2");
		    $signHere->setRecipientId("2");
		    // add the signature tab to the envelope's list of tabs
		    $tabs = new \DocuSign\eSign\Model\Tabs();
		    $tabs->setSignHereTabs(array($signHere));
		    // add a signer to the envelope
		    $signer = new \DocuSign\eSign\Model\Signer();
		    $signer->setEmail($recipientEmail);
		    $signer->setName($recipientName);
		    $signer->setRecipientId("2");
		    $signer->setTabs($tabs);
		    $signer->setClientUserId("1234");  // must set this to embed the recipient!
		    // Add a recipient to sign the document
		    $recipients = new \DocuSign\eSign\Model\Recipients();
		    $recipients->setSigners(array($signer));
		    $envelop_definition = new \DocuSign\eSign\Model\EnvelopeDefinition();
		    $envelop_definition->setEmailSubject("[DocuSign PHP SDK] - Please sign this doc");
		    // set envelope status to "sent" to immediately send the signature request
		    $envelop_definition->setStatus("sent");
		    $envelop_definition->setRecipients($recipients);
		    $envelop_definition->setDocuments(array($document));
		    // create and send the envelope! (aka signature request)
		    $envelop_summary = $envelopeApi->createEnvelope($accountId, $envelop_definition, null);
		    echo "$envelop_summary\n";



    		

		    /////////////////////////////////////////////////////////////////////////
		    // STEP 3:  Request Recipient View (aka signing URL)
		    /////////////////////////////////////////////////////////////////////////
		    // instantiate a RecipientViewRequest object
		    $recipient_view_request = new \DocuSign\eSign\Model\RecipientViewRequest();
		    // set where the recipient is re-directed once they are done signing
		    $recipient_view_request->setReturnUrl("https://www.docusign.com/develcenter");
		    // configure the embedded signer 
		    $recipient_view_request->setUserName($recipientName);
		    $recipient_view_request->setEmail($recipientEmail);
		    // must reference the same clientUserId that was set for the recipient when they 
		    // were added to the envelope in step 2
		    $recipient_view_request->setClientUserId("1234");
		    // used to indicate on the certificate of completion how the user authenticated
		    $recipient_view_request->setAuthenticationMethod("email");
		    // generate the recipient view! (aka embedded signing URL)
		    $signingView = $envelopeApi->createRecipientView($accountId, $envelop_summary->getEnvelopeId(), $recipient_view_request);
		    echo "Signing URL = " . $signingView->getUrl() . "\n";



	    }
        catch (DocuSign\eSign\ApiException $ex)
        {
            //echo "Exception: " . $ex->getMessage() . "\n";
        }
    }
}
