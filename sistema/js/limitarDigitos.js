function limitarDigitos(input, maxLength) {
    // Obtener el valor actual del input
    let valor = input.value;

    // Limitar la longitud del valor
    if (valor.length > maxLength) {
        valor = valor.slice(0, maxLength);
        input.value = valor;
    }
}
