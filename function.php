function get_total_sales_by_product_id($product_id){

// глобальная переменная $wpdb для выполнения запросов к БД
global $wpdb;

// даты "от" и "до" - получаем, например, из формы в админке
$date_to = $_POST['toDate'];
$date_from = $_POST['fromDate'];

// первый запрос - находим order_items с заданным ID продукта
$sql = "SELECT `order_item_id` AS item_ids
FROM `{$wpdb->prefix}woocommerce_order_itemmeta`
WHERE `meta_key` = '_product_id'
AND `meta_value` = '$product_id'
";

// второй запрос - находим order_items за требуемый период времени
$sql_2 = "SELECT `order_item_id` 
FROM `{$wpdb->prefix}woocommerce_order_items`
WHERE `order_item_type` = 'line_item'
AND `order_id` IN (
SELECT posts.ID AS post_id
FROM {$wpdb->posts} AS posts
WHERE posts.post_type = 'shop_order'
AND posts.post_status IN ('wc-completed','wc-processing', 'wc-custom-status', 'wc-on-hold')
AND DATE(posts.post_date) BETWEEN '$date_from' AND '$date_to')
";


$product_items = $wpdb->get_col($sql);
$date_items = $wpdb->get_col($sql_2);

if (!empty($product_items) && !empty($date_items)){

// находим совпадающие order_items из обоих запросов
    $items = array_intersect($product_items, $date_items);

// для каждого order_item находим все значения "количество в заказе"
    foreach ($items as $item){

        $sql_3 = "SELECT `meta_value`
        FROM `{$wpdb->prefix}woocommerce_order_itemmeta`
        WHERE `order_item_id` = '$item'
        AND `meta_key` = '_qty'
        "; 
        $qty[] = (int)$wpdb->get_var($sql_3);
    }

    if (!empty($qty)){
// суммируем количества
        $count = array_sum($qty);
    } else {
        $count = 0;
    }
        
    return $count;
    }
}
