# FreePBX Wake Up Calls for Asterisk
This is a port of the Wake Up Calls app on FreePBX for Vanilla Asterisk. Instead of relying on FreePBX to create the .call files in outgoing, this verison will create the .call files directly.

## Dependencies
- AGI
- PHP
- PHP cli
- pbx_spool.so (which handles .call files in /var/spool/asterisk/outgoing).
- Standard Asterisk sound files

## Verisons
There are 3 verisons available:
* 15 is a ported from FreePBX 15 and rewritten to support PHP 7.4+. The FreePBX 15 wakeup app does not play hello&this-is-yr-wakeup-call when first dialed, and is a little bit faster. Also, it's wakeconfrim.php has the option to "cancel" the wake up call.
* 15+16/17 takes the main wakeup app from FreePBX 16/17 and the wakeconfrim.php from FreePBX 15.
* 16/17 uses the both the main wakeup app and from FreePBX 16/17. The wakeupapp is a better slower, and the wakeconfrim.php only gives the optioins to snooze. However, the call can be canncled by simply hanging up.

## How it works

The Wake Up Calls app usally relys on FreePBX to schedule the wake up calls. This verison creates the .calls directly, which pbx_spool.so will process. This verison assumes that Asterisk is running under asterisk:asterisk. If you are running it under a diffrent user/group, you will need to change it. Also things like the ring and try time are hardcoded. But they can be changed. 

## How to install
