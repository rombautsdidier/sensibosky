# Changelog Sensibo Sky

> Reminder: if there is not information about the update, it concerns then only an update from the documentation, traslation or text modifications.

## 17/12/2022

Add command from the Sensibo Air
Modify layout of the command's list changed since 4.2 version


## 16/10/2020

Error correction in a function that force the mode if the target temperature and the chosen mode is not coherent. For example, if the temperature is 21°, the target temperature is 24° and the mode cool is chosen, the plugin will force heat mode. 

Add an option in the equipment to activate or not the "force mode" function and set a temperature delta in order to decide if the mode is forced or not.

Add the step parameter on slider to have 1° step and not 0.5° by default. For this, wait one minute after updating the plugin before having the new step applied.

## 21/07/2020

Take in account the capabilities list given by the module in order to create only the necessary.
The actual equipments must be deleted in order to recreate correctly the right commands.

## 27/06/2020

First plugin's version.
