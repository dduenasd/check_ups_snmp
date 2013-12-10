##############################

Información Instalación plugin check_ups_snmp

Requerimientos
-  Net-SNMP

Este plugin es un script de linux, para instalarlo hay que copiarlo en la ruta de los plugins de nagios
(habitualmente en /usr/local/nagios/libexec) y definir un comando de nagios en el command.cfg de nagios.

Definición en command.cfg de nagios:

define command {
        command_name                    check_ups_snmp
        command_line                    $USER1$/check_ups_snmp.sh -H $HOSTADDRESS$ -p $ARG1$ -C $ARG2$ -w $ARG3$ -c $ARG4$
        register                        1
}

Una vez hecho ésto, habrá que definir un servicio para cada parámetro a monitorizar en el archivo de configuración de servicios:

define service {
        host_name                       UPS_1
        service_description             ups_alarm
        use  							ups_template
        check_command                   check_ups_snmp!snmp!ups_alarm!0!0!-d /usr/local/nagios/libexec/mibs
        register                        1
}

Para el correcto uso de la opción ups_alarm, se necesitan los mibs incluidos en el directorio /mibs, éstos son necesarios para 
interpretar correctamente las alarmas que pueda dar la ups por snmp, en éste caso, como se ve en el ejemplo de definición de 
servicio, se han copiado los archivos de mibs en la ruta '/usr/local/nagios/libexec/mibs' pero podrían estar en cualquier otra.

Se incluyen plantillas para gráficos de pnp4nagios, para instalarlas, se debe copiar el fichero check_ups_snmp.php en el directorio /share/templates 
dentro del directorio de pnp4nagios.

  