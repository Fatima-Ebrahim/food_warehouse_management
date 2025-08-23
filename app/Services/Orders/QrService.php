<?php
namespace App\Services\Orders;



use App\Http\Resources\OrderDetailsResource;
use App\Models\Order;
use App\Repositories\Costumer\OrderRepository;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class QrService{

    public function __construct(protected OrderRepository $orderRepository)
    {
    }

    public function generateQr(Order $order, $userId)
    {
        $qrData = json_encode([
            'user_id' => $userId,
            'order' => new OrderDetailsResource($order),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);



        $qrCode = new QrCode($qrData);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(300);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $path = "qrcodes/order_{$order->id}.png";
        Storage::disk('public')->put($path, $result->getString());

        $order->update(['qr_code_path' => $path]);
    }





}
