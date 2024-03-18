<!DOCTYPE html>
<html>
<head>
    <title>Pickup Ticket Document</title>
</head>
<body>
    <?php
            foreach($transportData as $transportData){ ?>
<p><?=$transportData->name ?? null;?></p>
        <table border="1">
            <thead>
                <th>Sr. No.</th>
                <th>Order No.</th>
                <th>Retailer Name</th>
                <th>Product Name</th>
                <th>Batch Number</th>
                <th>Aisle Name</th>
                <th>Shelf Name</th>
                <th>Order Position</th>
                <th>Shipped Quantity</th>
            </thead>
            <tbody>
            <?php 
             $i = 1; 
            foreach($transportData->orderShipmentsDesc as $orderShipment){
            foreach($orderShipment->shipmentOrderItems as $shipmentOrderItem) { ?>
                <tr>
                    <td><?=$i++;?></td>
                    <td><?=$shipmentOrderItem->orderItems->order->order_reference; ?></td>
                    <td><?=$shipmentOrderItem->orderItems->order->retailerInformation->full_name; ?></td>
                    <td><?=$shipmentOrderItem->orderItems->product->product_name; ?></td>
                    <td><?=$shipmentOrderItem->batch_number; ?></td>
                    <td><?=$shipmentOrderItem->aisle_name; ?></td>
                    <td><?=$shipmentOrderItem->shelf_name; ?></td>
                    <td><?=$orderShipment->order_position; ?></td>
                    <td><?=$shipmentOrderItem->shipped_quantity; ?></td>
                </tr>
            <?php } }?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>