<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class processImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //return (new \App\Http\Controllers\Api\AuthController)->processOnlyImages();
        
        $filename = 'futbol' . date('Ymd_Hi') .'.csv';
        
        create_file($filename, ',1.png,,,,,,,,,,,,,,,,,,,,,,,,,,,2.png,,,,,,,,,,,,,,,,,3.png,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,4.png,,,,,,,,,,,,,,,,,,5.png,,,6.png,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,7.png,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,8.png,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,9.png');
        create_file($filename, 'pagina,LOCAL,EMPATE,VISITA,LE,EV,LV,MAS 2.5,MENOS 2.5,AMBOS SI,AMBOS NO,LOCAL AMBOS SI,LOCAL AMBOS NO,VISITA AMNBOS SI,VISITA AMBOS NO,EMPATE AMBOS SI,EMPATE AMBOS NO,LOCAL-LOCAL,LOCAL-EMPATE,LOCAL-VISITA,EMPATE-LOCAL,EMPATE-EMPATE,EMPATE-VISITA,VISITA-LOCAL,VISITA-EMPATE,VISITA-VISITA,LINEA GOL L,LINEA GOL V,LE NO VALIDO,V E NO VALIDO,H ASIATICO L,H ASIATICO E,H ASIATICO V,R.H.A L1,R. H. A. L2,R.H.A L3,R. H. A. L4,R. H. A. E1,R. H. A. E2,R. H. A. E3,R. H. A. E4,R. H. A. V1,R. H. A. V2,R. H. A. V3,R. H. A. V4,L.G.A 0.5 +,L.G.A 0.5 -,L.G.A 0.5 1.0 +,L.G.A 0.5 1.0 -,L.G.A 1.0 +,L.G.A 1.0 -,L.G.A 1.0 1.5 +,L.G.A 1.0 1.5 -,L.G.A 1.5 +,L.G.A 1.5 -,L.G.A 1.5 2.0 +,L.G.A 1.5 2.0 -,L.G.A 2.0 +,L.G.A 2.0 -,L.G.A 2.0 2.5 +,L.G.A 2.0 2.5 -,L.G.A 2.5 +,L.G.A 2.5 -,L.G.A 2.5 3.0 +,L.G.A 2.5 3.0 -,L.G.A 3.0 +,L.G.A 3.0 -,L.G.A 3.0 3.5 +,L.G.A 3.0 3.5 -,L.G.A 3.5 +,L.G.A 3.5 -,L.G.A 3.5 4.0 +,L.G.A 3.5 4.0 -,L.G.A 4.0 +,L.G.A 4.0 -,L.G.A 4.0 4.5 +,L.G.A 4.0 4.5 -,L.G.A 4.5 +,L.G.A 4.5 -,L.G.A 4.5 5.0+,L.G.A 4.5 5.0-,L.G.A 5.0+,L.G.A 5.0-,L.G.A 5.0 5.5+,L.G.A 5.0 5.5-,L.G.A 5.5+,L.G.A 5.5-,L.G.A 5.5 6.0+,L.G.A 5.5 6.0-,L.G.A. 6.0+,L.G.A. 6.0-,L.G.A 6.0 6.5+,L.G.A 6.0 6.5-,L.G.A 6.5+,L.G.A 6.5-,L.G.A 6.5 7.0+,L.G.A 6.5 7.0-,L.G.A. 7.0+,L.G.A. 7.0-,L.G.A 7.0 7.5+,L.G.A 7.0 7.5-,LOCAL HT,EMPATE HT,VISITA HF,LE HT,EV HT,LV HT,LOCAL AMBOS HF SI,LOCAL AMBOS HF NO,VISITA AMBOS HF SI,VISITA AMBOS HF NO,EMPATE AMBOS HF SI,EMPATE AMBOS HF NO,LOCAL GOLES MAS HF,LOCAL GOLES MENOS HF,VISITA GOLES MAS HF,VISITA GOLES MENOS HF,EMPATE GOLES MAS HF,EMPATE GOLES MENOS HF,1 MITAD MAS GOLES,2 MITAD MAS GOLES,EMPATE,MAS 0.5,MENOS 0.5,MAS 1.5,MENOS 1.5,MAS 3.5,MENOS 3.5,MAS 4.5,MENOS 4.5,MAS 5.5,MENOS 5.5,MAS 6.5,MENOS 6.5,MAS 7.5,MENOS 7.5,MAS 8.5,MENOS 8.5,MAS 9.5,MENOS 9.5,MAS 10.5,MENOS 10.5,MAS 11.5,MENOS 11.5,L MAS DE 2.5,L MENOS DE 2.5,V MAS 2.5,V MENOS 2.5,E MAS 2.5,E MENOS 2.5,MAS DE 2.5 YSI ,MAS DE 2.5 Y NO,MENOS DE 2.5 YSI,MENOS DE 2.5 Y NO,0 GOL,1 GOL,2 GOL,3 GOL,4 GOL,5 GOL,6 GOL,7 O MAS GOLES,MENOS 2 GOLES,2 O 3,MAS DE 3 GOLES,AMBOS EQUIPOS,LOCAL SOLO,SIN GOL,VISITA SOLO,1a MITAD - AMBOS ANOTARAN/SI,1a MITAD - AMBOS ANOTARAN/NO,2a MITAD - AMBOS ANOTARAN/SI,2a MITAD - AMBOS ANOTARAN/NO,AMBOS SI/SI,AMBOS SI/NO,AMBOS NO /SI,AMBOS NO/NO,1/T -GOLES +0.5,1/T -GOLES -0.5,1/T -GOLES +1.5,1/T -GOLES -1.5,1/T -GOLES +2.5,1/T -GOLES -2.5,1/T -GOLES +3.5,1/T -GOLES -3.5,1/T -GOLES +4.5,1/T -GOLES -4.5,1/T -GOLES +5.5,1/T -GOLES -5.5,1/M 0 GOLES,1/M 1 GOL,1/M 2 GOLES,1/M 3 GOLES,1/M 4 GOLES,1/M 5 GOLES +,EQUIPO ANOTARA/L,EQUIPO ANOTARA/0,EQUIPO ANOTARA/V,G/TEMPRANO-MIN,DATO,S/G TEMPRANO-MIN,DATO,G/TARDIO-MIN,DATO,S/G TARDIO-MIN,DATO,1-10,11-20,21-30,31-40,41-50,51-60,61-70,71-80,81-PITIDO FINAL,SIN GOL,0.5+,0.5-,1.5+,1.5-,2.5+,2.5-,3.5+,3.5-,4.5+,4.5-,5.5+,5.5-,6.5+,6.5-,2/M 0 GOLES,2/M 1 GOL,2/M 2 GOLES,2/M 3 GOLES,2/M 4 GOLES,2/M 5 GOLES +,L-1/M,L-2/M,L/EMPATE,V-1/M,V-2/M,V/EMPATE,P. L. SI,P. L. NO,P. V. SI,P. V. NO,L + DE 0.5,L - DE 0.5,L + DE 1.5,L - DE 1.5,L + DE 2.5,L - DE 2.5,L + DE 3.5,L - DE 3.5,L + DE 4.5,L - DE 4.5,L + DE 5.5,L - DE 5.5,L + DE 6.5,L - DE 6.5,L + DE 7.5,L - DE 7.5, L + DE 8.5,L - DE 8.5 ,L + DE 9.5,L - DE 9.5,V+ DE 0.5,V- DE 0.5,V+ DE 1.5,V- DE 1.5,V+ DE 2.5,V- DE 2.5,V+ DE 3.5,V- DE 3.5,V+ DE 4.5,V- DE 4.5,V+ DE 5.5,V- DE 5.5,V+ DE 6.5,V- DE 6.5,V+ DE 7.5,V- DE 7.5,LOCAL 0 GOL,LOCAL 1 GOL,LOCAL 2 GOLES,LOCAL 3 O MAS GOLES,VISITA 0 GOL,VISITA 1 GOL,VISITA 2 GOLES,VISITA 3 O MAS GOLES,M.V.1L,M.V.1V,M.V.2L,M.V.2V,M.V.3L,M.V.3V,M.V.4+L,M.V.4+V,M.V.EMPATE,M.V.SIN GOL,L. GOL MIN,DATO,L.SIN GOL MIN,DATO,V. GOL MIN,DATO,V.SIN GOL MIN,DATO,G/IMPAR,G/PAR,LOCAL PAR,LOCAL IMPAR,VISITA PAR,VISITA IMPAR,1G/IMPAR,1G/PAR,EQUIPO ANOTARA/L,EQUIPO ANOTARA/0,EQUIPO ANOTARA/V,REULTADO HF,RESULTADO FINAL,CORNERS,CORNERS LOCAL,CORNERS VISITA,TARJETA AMARILLA,TARJETA ROJA');

        return (new \App\Http\Controllers\Api\AuthController)->scanImages($filename);
    }
}
