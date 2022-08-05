<?php
$activation= (array_key_exists('activation-info-base64', $_POST) 
			  ? base64_decode($_POST['activation-info-base64']) 
			  : array_key_exists('activation-info', $_POST) ? $_POST['activation-info'] : '');

if(!isset($activation) || empty($activation)) { exit('make sure device is connected'); }


$encodedrequest = new DOMDocument;
$encodedrequest->loadXML($activation);
$activationDecoded= base64_decode($encodedrequest->getElementsByTagName('data')->item(0)->nodeValue);

$decodedrequest = new DOMDocument;
$decodedrequest->loadXML($activationDecoded);
$nodes = $decodedrequest->getElementsByTagName('dict')->item(0)->getElementsByTagName('*');

for ($i = 0; $i < $nodes->length - 1; $i=$i+2)
{

	switch ($nodes->item($i)->nodeValue)
	{
		case "ActivationRandomness": $activationRandomness = $nodes->item($i + 1)->nodeValue; break;
		case "DeviceClass": $deviceClass = $nodes->item($i + 1)->nodeValue; break;
		case "SerialNumber": $serialNumber = $nodes->item($i + 1)->nodeValue; break;
		case "UniqueDeviceID": $uniqueDiviceID = $nodes->item($i + 1)->nodeValue; break;
		case "MobileEquipmentIdentifier": $meid = $nodes->item($i + 1)->nodeValue; break;
		case "InternationalMobileEquipmentIdentity": $imei = $nodes->item($i + 1)->nodeValue; break;
		case "ActivationState": $activationState = $nodes->item($i + 1)->nodeValue; break;
		case "ProductVersion": $productVersion = $nodes->item($i + 1)->nodeValue; break;
	}
}


$snLength = strlen($serialNumber);

if($snLength > 12){
	echo "Hmmm something isn't right don't ya think?";
	exit();
}
if($snLength < 11){
	echo "Hmmm something isn't right dont ya think?";
	exit();
}

$udidLength = strlen($uniqueDiviceID);
if($udidLength < 40){
	echo "Hmmm something isn't right don't ya think?";
	exit();
}
if($udidLength > 40){
	echo "Hmmm something isn't right don't ya think?";
	exit();
}

# -------------------------------------------- Sign account token -----------------------------------------

$accountToken2=
'{'.(isset($imei) ? "\n\t".'"InternationalMobileEquipmentIdentity" = "'.$imei.'";' : '').'
   '.(isset($meid) ? "\n\t".'"MobileEquipmentIdentifier" = "'.$meid.'";' : '').
	"\n\t".'"ActivityURL" = "https://albert.apple.com/deviceservices/activity";'.
	"\n\t".'"ActivationRandomness" = "'.$activationRandomness.'";'.
	"\n\t".'"UniqueDeviceID" = "'.$uniqueDiviceID.'";'.
	"\n\t".'"SerialNumber" = "'.$serialNumber.'";'.
	"\n\t".'"CertificateURL" = "https://albert.apple.com/deviceservices/certifyMe";'.
	"\n\t".'"PhoneNumberNotificationURL" = "https://albert.apple.com/deviceservices/phoneHome";'.
	"\n\t".'"WildcardTicket" = "MIICHAIBATALBgkqhkiG9w0BAQWgdzB1AzEAgn1X4NL5tuZIoIjDh5meADOm1sDvpHjtnI9OxfqdZlEDGLZMaeaPY6O+iD0xsB7TBEAaNS6IKhyHU2k9An41IulixP/uC3zWZOIqseSIUyRawhu7/emlH8rRXUWCRopOJks7LNaywlVGTUBGNf3LSRE4MW6fPwQkKcken0AE4QBaAJ9LFFSASxcGlMJDZKMdGYHornvS53uOn4drB5kAAYZyB0ifh20HmQABhnIHSJ+XOAczMTBWWlcAn5c9DAAAAADu7u7u7u7u75+XPgQBAAAAn5c/BAEAAACfl0AEAAAAAASBgKLIKQ5BrMkZh74IoXp54aESoz6VMZHiqvoBUIIOt/+lUqisfpGKOCqjBmE5X20sy6oTeRo9/wph/+BHPJacFE1CJfCMKITvJ2O4c8ZurHX23wOcEERvwCuSZ9gkDhVSm+veC4ZY84Cxma+owiuFquxltae//xxcj3Mv5DfUl6oAo4GdMAsGCSqGSIb3DQEBAQOBjQAwgYkCgYEA7To/ZNHoIJzBUgY0734vsgl+ACxDQ+f4quvmSrPAtgDENSZwaVrHXpF+cRKBABqkDa00YcENx2dtS1tuHLKDNn1zMZLaZRpiK9UeiMPNZL6mlg12BWLwVjlFOGED8U6pfXwOw6D/FCDRgvyGBn7wsw8sEa7AdlYmMHGmkvwgOP0CAwEAAQ==\n";'.
	"\n".
 '}';
$accountToken=
'{'.(isset($imei) ? "\n\t".'"InternationalMobileEquipmentIdentity" = "'.$imei.'";' : '').'
   '.(isset($meid) ? "\n\t".'"MobileEquipmentIdentifier" = "'.$meid.'";' : '').
	"\n\t".'"ActivityURL" = "https://albert.apple.com/deviceservices/activity";'.
	"\n\t".'"ActivationRandomness" = "'.$activationRandomness.'";'.
	"\n\t".'"UniqueDeviceID" = "'.$uniqueDiviceID.'";'.
	"\n\t".'"SerialNumber" = "'.$serialNumber.'";'.
	"\n\t".'"CertificateURL" = "https://albert.apple.com/deviceservices/certifyMe";'.
	"\n".
 '}';

$accountTokenBase64=base64_encode($accountToken);
$accountTokenBase642=base64_encode($accountToken2);
$private = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCzYmXsSN3d7UTU8f77wm9C0IIJAwCmAeixBwkmWxJl239RFe9P
RbOPzk0WHTiEARBXToxx4V7eZxR12kiaTG/wRWVm6Jy1okz0U8HsmGKQsJS+EvKg
rFx3FgdzclqXulBOZzBSHvAwTo+ypNPR+vhmeYeRL6HvTuZBjZQYKeDyzwIDAQAB
AoGBAKL7vzFND1CpWIXGDe9+vIpPWiaH9NngGCRoCRcxXejv4qCwtksnQDtjrMRv
7j55nPhGZPK/WuvlakCeAKM42eZF/q2gRBeAZJNQkSHBW9d/OEt7bla92Fj+8IjP
A3cQ+eyo/KyNtF6OL9KE6ghMskKsGBkdMZkDJHMxVu+sK35pAkEA3QBbOwB4tPdK
4w+RwufoTmmSDxTGO5uvpsBRnFQ4K0s3WfPjhumDQRBeic+HxTDY72O1/iDpTbL9
pTW4f5qeswJBAM/K108a370DybA87FYVvMDOGBJsudIzLLhNj4eP4pO2+Dai955Y
qXTF1ntlOX7lD73QYFyrfrvMqWj43i3laXUCQFUymvkPAHm7T+pjCS1bW+pGtqEL
wDQgm8GsKIocyZ6fG5KY/CD5irkdh2SXVd8GKst25CU5KNfkZfY31I2U3RMCQQC4
DqGHNXPH1ooZrO1fF2QZmLSj5WD3u1K6ciFX3/DADUtyAgq6XSjFAdUJelFigH3g
Eaq5i0L4EMJi9EbBertdAkAdMef5SNkge26nq7nylq0/mVA0sEPTA/bSAMrZDVgV
4UBLXq12y1pQArJ/8rzkdL4x6fak50qzupAa/Jer8kie
-----END RSA PRIVATE KEY-----';
$pkeyid = openssl_pkey_get_private($private);
$pkeyid2 = openssl_pkey_get_private($private);

openssl_sign($accountToken, $signature, $pkeyid);
openssl_free_key($pkeyid);

openssl_sign($accountToken2, $signature2, $pkeyid2);
openssl_free_key($pkeyid2);
# -------------------------------------------------------------------------------------------------


$accountTokenSignature= base64_encode($signature);
$accountTokenSignature2= base64_encode($signature2);
$accountTokenCertificateBase64 = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSURaekNDQWsrZ0F3SUJBZ0lCQWpBTkJna3Foa2lHOXcwQkFRVUZBREI1TVFzd0NRWURWUVFHRXdKVlV6RVQKTUJFR0ExVUVDaE1LUVhCd2JHVWdTVzVqTGpFbU1DUUdBMVVFQ3hNZFFYQndiR1VnUTJWeWRHbG1hV05oZEdsdgpiaUJCZFhSb2IzSnBkSGt4TFRBckJnTlZCQU1USkVGd2NHeGxJR2xRYUc5dVpTQkRaWEowYVdacFkyRjBhVzl1CklFRjFkR2h2Y21sMGVUQWVGdzB3TnpBME1UWXlNalUxTURKYUZ3MHhOREEwTVRZeU1qVTFNREphTUZzeEN6QUoKQmdOVkJBWVRBbFZUTVJNd0VRWURWUVFLRXdwQmNIQnNaU0JKYm1NdU1SVXdFd1lEVlFRTEV3eEJjSEJzWlNCcApVR2h2Ym1VeElEQWVCZ05WQkFNVEYwRndjR3hsSUdsUWFHOXVaU0JCWTNScGRtRjBhVzl1TUlHZk1BMEdDU3FHClNJYjNEUUVCQVFVQUE0R05BRENCaVFLQmdRREZBWHpSSW1Bcm1vaUhmYlMyb1BjcUFmYkV2MGQxams3R2JuWDcKKzRZVWx5SWZwcnpCVmRsbXoySkhZdjErMDRJekp0TDdjTDk3VUk3ZmswaTBPTVkwYWw4YStKUFFhNFVnNjExVApicUV0K25qQW1Ba2dlM0hYV0RCZEFYRDlNaGtDN1QvOW83N3pPUTFvbGk0Y1VkemxuWVdmem1XMFBkdU94dXZlCkFlWVk0d0lEQVFBQm80R2JNSUdZTUE0R0ExVWREd0VCL3dRRUF3SUhnREFNQmdOVkhSTUJBZjhFQWpBQU1CMEcKQTFVZERnUVdCQlNob05MK3Q3UnovcHNVYXEvTlBYTlBIKy9XbERBZkJnTlZIU01FR0RBV2dCVG5OQ291SXQ0NQpZR3UwbE01M2cyRXZNYUI4TlRBNEJnTlZIUjhFTVRBdk1DMmdLNkFwaGlkb2RIUndPaTh2ZDNkM0xtRndjR3hsCkxtTnZiUzloY0hCc1pXTmhMMmx3YUc5dVpTNWpjbXd3RFFZSktvWklodmNOQVFFRkJRQURnZ0VCQUY5cW1yVU4KZEErRlJPWUdQN3BXY1lUQUsrcEx5T2Y5ek9hRTdhZVZJODg1VjhZL0JLSGhsd0FvK3pFa2lPVTNGYkVQQ1M5Vgp0UzE4WkJjd0QvK2Q1WlFUTUZrbmhjVUp3ZFBxcWpubTlMcVRmSC94NHB3OE9OSFJEenhIZHA5NmdPVjNBNCs4CmFia29BU2ZjWXF2SVJ5cFhuYnVyM2JSUmhUekFzNFZJTFM2alR5Rll5bVplU2V3dEJ1Ym1taWdvMWtDUWlaR2MKNzZjNWZlREF5SGIyYnpFcXR2eDNXcHJsanRTNDZRVDVDUjZZZWxpblpuaW8zMmpBelJZVHh0UzZyM0pzdlpEaQpKMDcrRUhjbWZHZHB4d2dPKzdidFcxcEZhcjBaakY5L2pZS0tuT1lOeXZDcndzemhhZmJTWXd6QUc1RUpvWEZCCjRkK3BpV0hVRGNQeHRjYz0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQo=';
$fairPlayKeyData = 'LS0tLS1CRUdJTiBDT05UQUlORVItLS0tLQpBQUVBQWQxcUNvNXAyWVo2bm9pTkVsNkI4cEFKbjBQZmxudHFPVklnckJDdDJXakNMdkpCL3V0TW5lZ01sUHBXClBjWjFNQWoyVHk0aGpIQms1OCtUbmE0azZRNVBLTDdQcFJvSlFkOFNFZjFTOERNdTBFclp6bDAvT3BwK2ZtTG4KYXBtcHhXeWVRcGJ6V0c3ZGh0aDFuRmxKam9GNnhlMmMyaTlMVGdlb3UzR05GaERURGhjQlczdkRIT2dGN01wbgpnaXNsWmxDYVRpQ1JIenNERkdNNzVzSjVBK1d6bUpucWpWTXA1RDZtVzM1U1NqTVhuVFNwajRwYmh4dlJlcnV4Ck9Uak1BS254MzgwbEM2bkIycDVsRGZyemdWQ1pTMmwxSW9ETlBkbm9KMjJ4RFpSWEx5d3RpemhZU0hBYStYcGYKd3daWklvWjM3YlcwTTl3TjlVWWQxVTE5MEw1QmdSNjhxYjEyVXQzbEFnVGNjVDRzOVo2Z01ZbGNVRGZsK2Nuawo4dW5qT3lkUVNvSUtJZWZEaGRsMkRibkdaUW04eXVzRlJBTnNRR2xkYzIzeGJTU3VPRFo3UWNmbEVjWnFUcUh3CjU4TytFUllwZmExVjZVbmh2VmlJTERrRTlhbkJFTm9oSnBKeHJqcG12dkc0ZlR0YVlYWlFuOXhZbHEvQktMa1kKdXg2REdUNnFsbnpBdlJqM1dpMUxFeWVFUGNJWnYxRVVoblhPRTgxU0puTVY0RTdsLzRqdy9WOEtRdmxWR3JUVQpwZjNQYVBBbVhsK29IZGlaK3ZRc3g0VzRZT1NLbE0wVFNQM04rb3FWblRWNUNxbFJkM09vVnRYV0dZSTMyM3VnCmZrM2VHM21VLytPMFUxS3Fvd1JFTVVHMjBaWWlxbUprdVE0VmJLTHFuUnZFc2FLZ0F6RDlsbWF3WGFtOWt2QkcKOVhEUUFJK3VoSWVuSWNYVFU0dUVLMWc4NmJNYzdwaHI0WGljWFlpampmTFY0elFtOGhyNlRwWVVtWlJXY2E4YgpaZ0FIN3BKMGJtUnA1aWF6OXRaMDd2Njh5ZnkxeGVNdGxaRkwxTGRVNHRrUmdCK2ZNbnRKWU9FWkV4ei9ETkM4CjNWNWZsV0VDSDRjL1d5dU5jd0o4OGhYVWRDcW9nYjU3bEdiM0NnT0tKOU9UMEdIdXBVY1NBVDY1RU5CcEcvZXEKME1mdVR5TnovZ25HRnFYTUFWaDcwWlUvOHVuT2dIK3pMdWRKdDBkUjR0SDBLWVVjUTdFSGtPTmNKMHNwQk5vcAplYmpBM2RYeGJXaXlSd28reDJaUlp1bFI3TlhRcUFESEZRY2JXcmFZRXI5MjhveGh6SDZkZC96WlBYalBVZE9RCkN0TVRUbGE4RXlXU1p1dFVNdjJFUmFEYytrWkU2bkx1a2pKbEdrUlR1dkhNQnNzYzI0dHcwS1pDbE5FRXp4N2cKNy9Remc0d3Q2Snpsb25tTC9hOVhjSVpoYzB1QXZsTk5CK0w1LzJFeFJhdVZZSTB4U1lkYkFXK3FlWGF2M1hsZwp2VEV5MTFHakY4WmMzaXc4NkdZclhqMmx3QjRpZDJWRFFlQ2xMWGdRaTBId0tTdXkvRGk4K0JmM2dKVm1tSUFoCjUzWTVBaGY4bHUyM3VHMkJSYjlZa29UdWpLMXdzYWdVWWZOb2wxUDlZSUpFUVVOeTFOWFZaUVpNQVZ5UmJUeDcKaHREUFI5anFNK1MzcjQ4SXo3LzEwYkpuWWlKSC8zODRoT3VRR1F6NDlWbGhQbEtDRXFVS25OR1N6emlid3BITgpURkZoT1VLVDc2NFVDNmY4STExelBpWW1WbFlnRVFseVBDL0lxM0pod2M1NFNOS3hzcm9zaHZRN0swQVZ5bnVFCjhqRXgxZjlaTWtvOTU0eTU1TlhaWmh4ZWRmOTFNRklZMHQrVTFMNUp6ODcvQ1FOWnI2emdKZ2FyU2tEN244TG0KcUR1ZWV1KzBEWjdra05kdnV2b1VmNzJOamc2MDk2RG9VdHhOZURwa1NKRmFSSGNVCi0tLS0tRU5EIENPTlRBSU5FUi0tLS0tCg==';
$deviceCertificate = 'LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUM4ekNDQWx5Z0F3SUJBZ0lLQklYbG4wMEdIZXppN0RBTkJna3Foa2lHOXcwQkFRVUZBREJhTVFzd0NRWUQKVlFRR0V3SlZVekVUTUJFR0ExVUVDaE1LUVhCd2JHVWdTVzVqTGpFVk1CTUdBMVVFQ3hNTVFYQndiR1VnYVZCbwpiMjVsTVI4d0hRWURWUVFERXhaQmNIQnNaU0JwVUdodmJtVWdSR1YyYVdObElFTkJNQjRYRFRFNE1EVXhOekl6Ck5EZ3pOMW9YRFRJeE1EVXhOekl6TkRnek4xb3dnWU14TFRBckJnTlZCQU1XSkRJMVJEVkZNelJETFVSRk56QXQKTkRaRVFTMUJRelZFTFVJeVJFVkJRemMwTWpBM1JqRUxNQWtHQTFVRUJoTUNWVk14Q3pBSkJnTlZCQWdUQWtOQgpNUkl3RUFZRFZRUUhFd2xEZFhCbGNuUnBibTh4RXpBUkJnTlZCQW9UQ2tGd2NHeGxJRWx1WXk0eER6QU5CZ05WCkJBc1RCbWxRYUc5dVpUQ0JuekFOQmdrcWhraUc5dzBCQVFFRkFBT0JqUUF3Z1lrQ2dZRUF2Nmt6V1BVRkE0REIKTzdGR1ZRbXR3blUvbVY5TTJrWkRkNmZWTmtFOUU4K0hMelp0cWNNeHRvL0FSaEVJWkhHTWdiSUcrR3llMzNQUwpTTlBPVDNOMWdMdmhRN1VMSkhlVUhQL1pvYlNPWk83OXYvMDlLd2RvV1pzWm13a3NpWnVmR01WYjYzMVIzMkw1ClFHTzZ5bkJTRXgya3JwWHpOVzJYRjAva0VlaGlGTjBDQXdFQUFhT0JsVENCa2pBZkJnTlZIU01FR0RBV2dCU3kKL2lFalJJYVZhbm5WZ1NhT2N4RFlwMHlPZERBZEJnTlZIUTRFRmdRVVc2d1BPNERLZ0NnU0FmZkRZQWJ3dzVITQpmK0F3REFZRFZSMFRBUUgvQkFJd0FEQU9CZ05WSFE4QkFmOEVCQU1DQmFBd0lBWURWUjBsQVFIL0JCWXdGQVlJCkt3WUJCUVVIQXdFR0NDc0dBUVVGQndNQ01CQUdDaXFHU0liM1kyUUdDZ0lFQWdVQU1BMEdDU3FHU0liM0RRRUIKQlFVQUE0R0JBR08vV25MK3lhbXhMYXZMWG53VVZWQVNNUE8xOGhma3Q2RzgxNWZucWErMXhhV2tZVnY2VHZQeApjVlZvVnZmcnIvZ2IrL2hjMGdFRm1iL2tlOTJVcEwvN1I4Vm9TL2NCSUhXVm44WFU3a2J1bTVaTlVxeVdMbU9lClZ4VW5kdHptaUFhaTg3VmFNMFFFMjA3ZWpUQVBDc1YwdVppaktwdmEvS0sxZmhlYUp5dTcKLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQo=';

$response ='<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="keywords" content="iTunes Store" /><meta name="description" content="iTunes Store" /><title>iPhone Activation</title><link href="http://static.ips.apple.com/ipa_itunes/stylesheets/shared/common-min.css" charset="utf-8" rel="stylesheet" /><link href="http://static.ips.apple.com/deviceservices/stylesheets/styles.css" charset="utf-8" rel="stylesheet" /><link href="http://static.ips.apple.com/ipa_itunes/stylesheets/pages/IPAJingleEndPointErrorPage-min.css" charset="utf-8" rel="stylesheet" /><link href="resources/auth_styles.css" charset="utf-8" rel="stylesheet" /><script id="protocol" type="text/x-apple-plist">
<plist version="1.0">
	<dict>
		<key>'.($deviceClass == "iPhone" ? 'iphone' : 'device').'-activation</key>
		<dict>
			<key>activation-record</key>
			<dict>
				<key>FairPlayKeyData</key>
				<data>'.$fairPlayKeyData.'</data>
				<key>AccountTokenCertificate</key>
				<data>'.$accountTokenCertificateBase64.'</data>
				<key>DeviceCertificate</key>
				<data>'.$deviceCertificate.'</data>
				<key>AccountTokenSignature</key>
				<data>'.$accountTokenSignature2.'</data>
				<key>AccountToken</key>
				<data>'.$accountTokenBase642.'</data>
			</dict>
			<key>unbrick</key>
			<true/>
			<key>show-settings</key>
			<true/>
		</dict>
	</dict>
</plist>
</script><script>var protocolElement = document.getElementById("protocol");var protocolContent = protocolElement.innerText;iTunes.addProtocol(protocolContent);</script></head>
</html>';
echo $response;
exit;
?>