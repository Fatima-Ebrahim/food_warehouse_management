<?php
namespace App\Services\Orders;



use App\Models\Installment;
use App\Models\Order;
use App\Repositories\Costumer\OrderRepository;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QrService{

    public function __construct(protected OrderRepository $orderRepository)
    {
    }

    public function generateQr(Order $order, $userId)
    {

        $qrData = json_encode([
            'order_id' => $order->id,
            'user_id' => $userId,
            'timestamp' => now()->timestamp,
        ]);

        // حماية من حجم البيانات الكبير
        if (strlen($qrData) > 1024) {
            throw new \Exception('QR data is too large');
        }

        $qrCode = new QrCode($qrData);
        $qrCode->setSize(300);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $path = "qrcodes/order_{$order->id}.png";
        Storage::disk('public')->put($path, $result->getString());

        $order->update(['qr_code_path' => $path]);
    }





}
