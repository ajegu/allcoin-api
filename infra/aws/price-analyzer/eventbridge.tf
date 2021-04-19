resource "aws_cloudwatch_event_rule" "price_analyzer" {
    name = "${var.app_name}${var.lambda_name}EventRule"
    description = "Get Binance price every minutes"
    schedule_expression = "rate(1 minute)"
}

resource "aws_cloudwatch_event_target" "price_analyzer" {
    arn = aws_lambda_function.price_analyzer.arn
    rule = aws_cloudwatch_event_rule.price_analyzer.name
}

