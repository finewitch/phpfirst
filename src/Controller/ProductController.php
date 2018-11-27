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

use Slince\Shopify\PrivateAppCredential;
use Slince\Shopify\Client;

use GuzzleHttp\Client as ClientGuz;
use GuzzleHttp\Psr7\Response as ResponseGuz;

use App\Service\FilterProducts;
use App\Service\SaveMetafields;


class ProductController extends AbstractController
{

    /**
     * @Route("/product", name="product")
     */

    public function new(FilterProducts $filterProducts, Request $request, SaveMetafields $saveMetafields)
// $.get('http://127.0.0.1:8000/product', funct)
    {
        // try {
        //     $response = [
        //         'status' => 'ok',
        //         'products' => $responseFilterProducts = $filterProducts->getAllProducts($request),
        //     ];
        // }
        // catch (\Exception $e) { 
        //         $response = [
        //             'status' => 'error',
        //             'msg' => $e->getMessage()
        //         ];
        // }
        // if($response['status'] == 'ok') {
        //     try {
        //         $responseMetafields = $saveMetafields->index(json_decode($responseFilterProducts));
        //        }
        //     catch (\Exception $e) { 
        //             $response = [
        //                 'status' => 'error',
        //                 'msg' => $e->getMessage(),
        //             ];
        //     }
        // }
            
        $response = [
                     'status' => 'ok',
                     'products' => ''
             ];
        

        return new Response(
            $response,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        
    }

}
