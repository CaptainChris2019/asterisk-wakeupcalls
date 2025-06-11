# FreePBX Wake Up Calls for Asterisk
This is a port of the Wake Up Calls app on FreePBX for Vanilla Asterisk. Instead of relying on FreePBX to create the .call files in outgoing, this verison will create the .call files directly.

## Dependencies
- AGI (res_agi.so)
- PHP
- PHP cli
- pbx_spool.so (which handles .call files in /var/spool/asterisk/outgoing).
- Standard Asterisk sound files

## Verisons
There are 2 verisons of each file:
* 15 is a ported from FreePBX 15 and rewritten to support PHP 7.4+. The FreePBX 15 wakeup app does not play hello&this-is-yr-wakeup-call when first dialed, and is a little bit faster. Also, it's wakeconfrim.php has the option to "cancel" the wake up call.
* 16/17 is fromFreePBX 16/17. The wakeup app is a better slower, and the wakeconfrim.php only gives the optioins to snooze. However, the call can be canncled by simply hanging up.

You can mix and match either one to your liking if you wish.

## How it works

The Wake Up Calls app usally relys on FreePBX to schedule the wake up calls. This verison creates the .calls directly, which pbx_spool.so will process. This verison assumes that Asterisk is running under asterisk:asterisk. If you are running it under a diffrent user/group, you will need to change it. Also things like the ring and try time are hardcoded. But they can be changed. 

## How to install
* Ensure PHP and php-cli is installed on your system
* Ensure res_agi.so and pbx_spool.so is loaded
* Place your preferred wakeup and wakeconfrim.php in /var/lib/asterisk/agi-bin
* Place the following in the context you use for outgoing calls in your dialplan
```
exten => *68,1,Set(__COS_DEST=hotelwakeup)
exten => *68,n,Set(__COS_TYPE=FC)
exten => *68,n,Macro(user-callerid,)
exten => *68,n,Macro(user-callerid,)
exten => *68,n,Answer
exten => *68,n,Wait(1)
exten => *68,n,AGI(wakeup)
exten => *68,n,Hangup
```
\*68 is the default code FreePBX uses. You can change it if you wish.
There is no need to add anything to the diaplan to handle wakeconfrim. The .call file points directly to wakeconfrim.php
