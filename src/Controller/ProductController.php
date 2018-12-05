<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

use Slince\Shopify\PrivateAppCredential;
use Slince\Shopify\Client;

use GuzzleHttp\Client as ClientGuz;
use GuzzleHttp\Psr7\Response as ResponseGuz;

use App\Service\FilterProducts;
use App\Service\SaveMetafields;


class WizardController extends AbstractController
{

    /**
     * @Route("/product", name="product")
     */

    public function new(FilterProducts $filterProducts, Request $request, SaveMetafields $saveMetafields)
    {
        try {
            $responseProducts = [
                'status' => 'ok',
                'products' => $responseFilterProducts = $filterProducts->getAllProducts($request),
            ];
        }
        catch (\Exception $e) { 
                $responseProducts = [
                    'status' => 'error',
                    'msg' => $e->getMessage()
                ];
        }

        if($responseProducts['status'] == 'ok' && count($responseProducts['products']) > 0 ) {
            // dump($responseFilterProducts);exit;
            try {
                $responseMetafields = $saveMetafields->index(json_decode($responseFilterProducts));

               }
            catch (\Exception $e) { 
                    $responseProducts = [
                        'status' => 'error',
                        'msg' => $e->getMessage(),
                    ];
            }
        }
        
        $response = new JsonResponse(
            $responseProducts,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );


        return $response;
        
    }

}
