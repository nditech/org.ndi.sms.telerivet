# Telerivet CiviCRM integration

This CiviCRM extension integrates https://telerivet.com/ allowing you to use send and receive SMS with CiviCRM anywhere that you can send and receive SMS using an Android or IOS phone.

## Installation

Download and install the extension as normal.
Sign up for an account with Telerivet.
Download the telerivet app to your phone.

## Configuration

You'll need to configure both CiviCRM and Telerivent. We'll do Telerivet first.

### Telerivet

1. Sign up for an account and add your phone number to your account
2. From your account pages, make a note of the following
  1. Your `API key`, which can be found on the ** Account > Developer API ** page.
  2. Your `Project ID` which can also be found on the ** Account > Developer API ** page
3. (if you are doing two way SMS), you'll need to register a webhook (a CiviCRM URL that will listen for incoming texts) in Telerivet and make a note of the secret associated with the webhook.
  1. In Services, select ** Add New Service **
  2. Choose ** Webhook API **
  3. Fill out the form and put the following URL (replacing example with your domain): http://example.org/civicrm/sms/callback?provider=org.ndi.sms.telerivet (note that the URL with be slightly different for Wordpress and Joomla). If you aren't sure what it should be, please file an issue in this repository.
  4. Make a note of the `secret`.

### CiviCRM

2. Configure a new SMS provider at civicrm/admin/sms/provider?reset=1
3. Choose Telerivet for the name.
4. Add your api key, project id and (if you are setting up two way SMS) your secret into the API parameters box as follows
```
api_key=##############################
project_id=#################
secret=################################
```

5. Other fields on the SMS provider form are not used, but are required by CiviCRM. So you can add anything into these fields.
