resource "aws_sns_topic" "binance_order_analyzer" {
    name = "${var.app_name}-${var.lambda_name}Topic"
}

output "AWS_SNS_TOPIC_ORDER_ANALYZER_ARN" {
    value = aws_sns_topic.binance_order_analyzer.arn
}
