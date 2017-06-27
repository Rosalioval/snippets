<?php

namespace KonfioSdk\Test\Mock;

class LambdaClientMock
{
    protected $contentsIndex = '';

    protected $contents = [
        'imagesToPdf' => [
            'Bucket' => 'clientsbucket-backup-dev',
            'Key' => 'CURP123456TEST/verification/ife.pdf',
        ],
        'set_verification_docs_DEV' => [
            'requiredDocs' => [
                [
                    'status' =>' ',
                    'approvedDate' =>' ',
                    'key' => '4',
                    'fileDescription' => 'Puede ser tu INE/ IFE (frente) o Pasaporte. Recuerda que debe estar vigente',
                    'uploadedDate' =>' ',
                    'label' => 'Identificación Oficial (Frente)',
                    'rejectionReason' =>' ',
                    'filename' => 'ife_frente_',
                ],
                [
                    'status' =>' ',
                    'approvedDate' =>' ',
                    'key' => '27',
                    'fileDescription' => 'Puede ser tu INE/ IFE (reverso) o Pasaporte. Recuerda que debe estar vigente',
                    'uploadedDate' =>' ',
                    'label' => 'Identificación Oficial (Reverso)',
                    'rejectionReason' =>' ',
                    'filename' => 'ife_atras_',
                ],
                [
                    'status' =>' ',
                    'approvedDate' =>' ',
                    'key' => '36',
                    'fileDescription' => 'Debe estar a tu nombre e incluir tu cuenta CLABE para que te depositemos',
                    'uploadedDate' =>' ',
                    'label' => 'Estado de Cuenta Bancaria',
                    'rejectionReason' =>' ',
                    'filename' => 'estado_de_cuenta_bancario_',
                ],
                [
                    'status' =>' ',
                    'approvedDate' =>' ',
                    'key' => '23',
                    'fileDescription' => 'El comprobante debe coincidir con la dirección que nos proporcionaste y tener menos de tres meses',
                    'uploadedDate' =>' ',
                    'label' => 'Comprobante de Domicilio',
                    'rejectionReason' =>' ',
                    'filename' => 'comprobante_domicilio_',
                ],
            ]
        ],
        'mergePdfs' => [
            'Bucket' => 'konfiobucket-dev',
            'Key' => '266396/ife.pdf',
            'ETag' => "04df3bbb3f791f986289079e3582d1a5",
            'VersionId' => 'lk8MiP5xMVZN6PU0jj7D5PKR5GWYBifm',
        ],
        'error' => [
            'errorMessage' => '',
        ]
    ];

    public function invoke(Array $config)
    {
        $this->contentsIndex = $config['FunctionName'];

        return $this;
    }

    public function get(String $type)
    {
        return $this;
    }

    public function getContents(): String
    {
        $contents = $this->contents[$this->contentsIndex];

        return json_encode($contents);
    }
}
