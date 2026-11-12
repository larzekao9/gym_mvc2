'==================================================================
' Script de Enterprise Architect (VBScript)
' DIAGRAMA DE CLASES - Patron Template Method: Pago de Membresia (gym mvc)
'
' Refleja el codigo tal cual: cada atributo y metodo con su visibilidad
' real (+ public / - private / # protected), static, const, abstract,
' final, tipos y valores por defecto.
'
'   PagoView ------------------> PagoController
'   PagoController ------------> PagoTemplate
'   PagoController ------------> PlanMembresiaModel / PagoModel / UsuarioModel
'   PagoMensual / PagoTrimestral / PagoAnual ---|> PagoTemplate (herencia)
'   Modelos -------------------> Conexion
'
' USO:
' 1. Scripting -> Scripts -> tu grupo -> New VBScript
' 2. Pega TODO y Save Script
' 3. En el Browser SELECCIONA el paquete destino
' 4. Clic derecho sobre el script -> Run Script
'==================================================================

option explicit

!INC Local Scripts.EAConstants-VBScript

' Colores (EA usa BGR: R + G*256 + B*65536)
Const NARANJA       = 11917818   ' #FAD9B5  vista, controlador, modelos, conexion
Const BORDE_NARANJA = 8945678    ' #0E8088
Const AZUL          = 16574682   ' #DAE8FC  template y subclases
Const BORDE_AZUL    = 12553836   ' #6C8EBF
Const ROJO          = 178        ' #B20000  asociaciones
Const AZUL_FLECHA   = 16711680   ' #0000FF  herencia del patron

Dim oDiagram
Dim oPackage

Dim clsView
Dim clsController
Dim clsTemplate
Dim clsMensual
Dim clsTrimestral
Dim clsAnual
Dim clsPlanModel
Dim clsPagoModel
Dim clsUsuarioModel
Dim clsConexion

Sub Main

    Set oPackage = Repository.GetTreeSelectedPackage()
    If oPackage Is Nothing Then
        Session.Prompt "Selecciona primero un paquete en el Browser.", 0
        Exit Sub
    End If

    ' ---------- Crear el diagrama de Clases ----------
    Set oDiagram = oPackage.Diagrams.AddNew("DC - Template Method Pago de Membresia", "Class")
    oDiagram.Update
    oPackage.Diagrams.Refresh

    ' ==============================================================
    ' PagoView   <- views/PagoView.php
    ' ==============================================================
    Set clsView = NuevaClase("PagoView", "Class", False)
    AddMet clsView, "render", "void", "clientes:array,planes:array,historial:array,recibo:?array,opciones:array=[]", "Public", False, False, False, 0

    ' ==============================================================
    ' PagoController   <- controllers/PagoController.php
    ' ==============================================================
    Set clsController = NuevaClase("PagoController", "Class", False)
    AddAttr clsController, "tipos", "array", "Public", False, False, "", 0
    AddAttr clsController, "usuarioModel", "UsuarioModel", "Public", False, False, "", 1
    AddAttr clsController, "planModel", "PlanMembresiaModel", "Public", False, False, "", 2
    AddAttr clsController, "pagoModel", "PagoModel", "Public", False, False, "", 3
    AddAttr clsController, "vista", "PagoView", "Public", False, False, "", 4
    AddMet clsController, "__construct", "", "usuarioModel:UsuarioModel,planModel:PlanMembresiaModel,pagoModel:PagoModel,vista:PagoView", "Public", False, False, False, 0
    AddMet clsController, "handle", "void", "metodo:string,datos:array", "Public", False, False, False, 1
    AddMet clsController, "procesarConTemplate", "array", "datos:array,preview:bool", "Private", False, False, False, 2

    ' ==============================================================
    ' PagoTemplate   <- templates/PagoTemplate.php  (clase abstracta)
    ' ==============================================================
    Set clsTemplate = NuevaClase("PagoTemplate", "Class", True)
    AddAttr clsTemplate, "BONO_PERMANENCIA", "float", "Public", True, True, "20.0", 0
    AddAttr clsTemplate, "usuario", "array", "Public", False, False, "", 1
    AddAttr clsTemplate, "plan", "array", "Public", False, False, "", 2
    AddAttr clsTemplate, "yaPagoHoy", "bool", "Public", False, False, "", 3
    AddAttr clsTemplate, "montoBase", "float", "Public", False, False, "", 4
    AddAttr clsTemplate, "bonoPermanencia", "float", "Public", False, False, "", 5
    AddAttr clsTemplate, "descuento", "float", "Public", False, False, "", 6
    AddAttr clsTemplate, "montoFinal", "float", "Public", False, False, "", 7
    ' templateMethod es final: ninguna subclase puede cambiar el orden de los pasos
    AddMet clsTemplate, "templateMethod", "array", "usuario:array,plan:array,yaPagoHoy:bool", "Public", False, False, True, 0
    AddMet clsTemplate, "validarPagoDuplicado", "void", "", "Protected", False, False, False, 1
    AddMet clsTemplate, "calcularMontoBase", "void", "", "Protected", False, False, False, 2
    AddMet clsTemplate, "aplicarBonoPermanencia", "void", "", "Protected", False, False, False, 3
    AddMet clsTemplate, "calcularDescuento", "float", "", "Public", False, True, False, 4
    AddMet clsTemplate, "generarRecibo", "array", "", "Public", False, True, False, 5

    ' ==============================================================
    ' Subclases concretas   <- templates/Pago*.php
    ' ==============================================================
    Set clsMensual = NuevaClase("PagoMensual", "Class", False)
    AddMet clsMensual, "calcularDescuento", "float", "", "Public", False, False, False, 0
    AddMet clsMensual, "generarRecibo", "array", "", "Public", False, False, False, 1

    Set clsTrimestral = NuevaClase("PagoTrimestral", "Class", False)
    AddMet clsTrimestral, "calcularDescuento", "float", "", "Public", False, False, False, 0
    AddMet clsTrimestral, "generarRecibo", "array", "", "Public", False, False, False, 1

    Set clsAnual = NuevaClase("PagoAnual", "Class", False)
    AddMet clsAnual, "calcularDescuento", "float", "", "Public", False, False, False, 0
    AddMet clsAnual, "generarRecibo", "array", "", "Public", False, False, False, 1

    ' ==============================================================
    ' PlanMembresiaModel   <- models/PlanMembresiaModel.php
    ' ==============================================================
    Set clsPlanModel = NuevaClase("PlanMembresiaModel", "Class", False)
    AddAttr clsPlanModel, "id", "int", "Public", False, False, "", 0
    AddAttr clsPlanModel, "nombre", "string", "Public", False, False, "", 1
    AddAttr clsPlanModel, "tipo", "string", "Public", False, False, "", 2
    AddAttr clsPlanModel, "precioBase", "float", "Public", False, False, "", 3
    AddAttr clsPlanModel, "mesesCompromiso", "int", "Public", False, False, "", 4
    AddAttr clsPlanModel, "conexion", "PDO", "Private", False, False, "", 5
    AddMet clsPlanModel, "__construct", "", "", "Public", False, False, False, 0
    AddMet clsPlanModel, "obtenerTodos", "array", "", "Public", False, False, False, 1
    AddMet clsPlanModel, "buscarPorId", "?array", "id:int", "Public", False, False, False, 2

    ' ==============================================================
    ' PagoModel   <- models/PagoModel.php
    ' ==============================================================
    Set clsPagoModel = NuevaClase("PagoModel", "Class", False)
    AddAttr clsPagoModel, "id", "int", "Public", False, False, "", 0
    AddAttr clsPagoModel, "usuarioId", "int", "Public", False, False, "", 1
    AddAttr clsPagoModel, "planId", "int", "Public", False, False, "", 2
    AddAttr clsPagoModel, "montoBase", "float", "Public", False, False, "", 3
    AddAttr clsPagoModel, "descuento", "float", "Public", False, False, "", 4
    AddAttr clsPagoModel, "bonoPermanencia", "float", "Public", False, False, "", 5
    AddAttr clsPagoModel, "montoFinal", "float", "Public", False, False, "", 6
    AddAttr clsPagoModel, "createdAt", "string", "Public", False, False, "", 7
    AddAttr clsPagoModel, "conexion", "PDO", "Private", False, False, "", 8
    AddMet clsPagoModel, "__construct", "", "", "Public", False, False, False, 0
    AddMet clsPagoModel, "registrar", "int", "d:array", "Public", False, False, False, 1
    AddMet clsPagoModel, "existePagoHoy", "bool", "usuarioId:int,planId:int", "Public", False, False, False, 2
    AddMet clsPagoModel, "obtenerHistorial", "array", "limit:int=20", "Public", False, False, False, 3

    ' ==============================================================
    ' UsuarioModel   <- models/UsuarioModel.php
    ' ==============================================================
    Set clsUsuarioModel = NuevaClase("UsuarioModel", "Class", False)
    AddAttr clsUsuarioModel, "id", "int", "Public", False, False, "", 0
    AddAttr clsUsuarioModel, "nombre", "string", "Public", False, False, "", 1
    AddAttr clsUsuarioModel, "apellido", "string", "Public", False, False, "", 2
    AddAttr clsUsuarioModel, "email", "string", "Public", False, False, "", 3
    AddAttr clsUsuarioModel, "password", "string", "Public", False, False, "", 4
    AddAttr clsUsuarioModel, "rol", "string", "Public", False, False, "", 5
    AddAttr clsUsuarioModel, "conexion", "PDO", "Private", False, False, "", 6
    AddMet clsUsuarioModel, "__construct", "", "", "Public", False, False, False, 0
    AddMet clsUsuarioModel, "crear", "void", "datos:array", "Public", False, False, False, 1
    AddMet clsUsuarioModel, "autenticar", "?array", "email:string,password:string", "Public", False, False, False, 2
    AddMet clsUsuarioModel, "obtenerTodos", "array", "", "Public", False, False, False, 3
    AddMet clsUsuarioModel, "obtenerPorId", "?array", "id:int", "Public", False, False, False, 4
    AddMet clsUsuarioModel, "actualizar", "void", "id:int,datos:array", "Public", False, False, False, 5
    AddMet clsUsuarioModel, "eliminar", "void", "id:int", "Public", False, False, False, 6

    ' ==============================================================
    ' Conexion   <- Conexion.php  (singleton)
    ' ==============================================================
    Set clsConexion = NuevaClase("Conexion", "Class", False)
    AddAttr clsConexion, "instancia", "?PDO", "Private", True, False, "", 0
    AddMet clsConexion, "getConexion", "PDO", "", "Public", True, False, False, 0

    ' ---------- Relaciones ----------
    ' AddAsoc origen, destino, color, ancho   (flecha hacia el destino)
    AddAsoc clsView, clsController, ROJO, 1
    AddAsoc clsController, clsTemplate, ROJO, 1
    AddAsoc clsController, clsPlanModel, ROJO, 1
    AddAsoc clsController, clsPagoModel, ROJO, 1
    AddAsoc clsController, clsUsuarioModel, ROJO, 1
    AddAsoc clsPlanModel, clsConexion, ROJO, 1
    AddAsoc clsPagoModel, clsConexion, ROJO, 1
    AddAsoc clsUsuarioModel, clsConexion, ROJO, 1
    ' AddHereda hija, padre   (triangulo vacio en el padre)
    AddHereda clsMensual, clsTemplate, AZUL_FLECHA
    AddHereda clsTrimestral, clsTemplate, AZUL_FLECHA
    AddHereda clsAnual, clsTemplate, AZUL_FLECHA

    ' ---------- Colocar en el diagrama (t y b negativos en EA) ----------
    AddObj clsView, 20, 360, -340, -440, 1, NARANJA, BORDE_NARANJA
    AddObj clsController, 420, 800, -300, -510, 2, NARANJA, BORDE_NARANJA
    AddObj clsTemplate, 520, 900, -580, -870, 3, AZUL, BORDE_AZUL
    AddObj clsMensual, 340, 580, -950, -1020, 4, AZUL, BORDE_AZUL
    AddObj clsTrimestral, 600, 840, -950, -1020, 5, AZUL, BORDE_AZUL
    AddObj clsAnual, 860, 1100, -950, -1020, 6, AZUL, BORDE_AZUL
    AddObj clsPlanModel, 1000, 1300, -20, -240, 7, NARANJA, BORDE_NARANJA
    AddObj clsUsuarioModel, 1000, 1300, -320, -610, 8, NARANJA, BORDE_NARANJA
    AddObj clsPagoModel, 1380, 1700, -160, -440, 9, NARANJA, BORDE_NARANJA
    AddObj clsConexion, 1800, 2060, -200, -280, 10, NARANJA, BORDE_NARANJA
    oDiagram.DiagramObjects.Refresh

    Repository.ReloadDiagram oDiagram.DiagramID
    Repository.OpenDiagram oDiagram.DiagramID

    Session.Prompt "Diagrama 'Template Method Pago de Membresia' creado (10 clases).", 0

End Sub

'------------------------------------------------------------------
' Crea una clase en el paquete (bAbstracta = True la marca abstract)
'------------------------------------------------------------------
Function NuevaClase(sNombre, sTipo, bAbstracta)
    Dim el
    Set el = oPackage.Elements.AddNew(sNombre, sTipo)
    If bAbstracta Then el.Abstract = "1"
    el.Update
    oPackage.Elements.Refresh
    Set NuevaClase = el
End Function

'------------------------------------------------------------------
' Atributo con visibilidad real, static, const y valor inicial
'------------------------------------------------------------------
Sub AddAttr(oClase, sNombre, sTipo, sVisibilidad, bStatic, bConst, sDefault, nPos)
    Dim a
    Set a = oClase.Attributes.AddNew(sNombre, sTipo)
    a.Visibility = sVisibilidad
    a.IsStatic = bStatic
    a.IsConst = bConst
    If sDefault <> "" Then a.Default = sDefault
    a.Pos = nPos
    a.Update
    oClase.Attributes.Refresh
End Sub

'------------------------------------------------------------------
' Metodo con parametros "nombre:tipo,nombre:tipo=valorPorDefecto"
'   bAbstract = metodo abstracto, bFinal = final (leaf en EA)
'   El metodo se guarda ANTES de agregarle parametros.
'------------------------------------------------------------------
Sub AddMet(oClase, sNombre, sRetorno, sParams, sVisibilidad, bStatic, bAbstract, bFinal, nPos)
    Dim m, p, partes, i, par, tipoDef
    Set m = oClase.Methods.AddNew(sNombre, sRetorno)
    m.Visibility = sVisibilidad
    m.IsStatic = bStatic
    m.Abstract = bAbstract
    m.IsLeaf = bFinal
    m.Pos = nPos
    m.Update

    If sParams <> "" Then
        partes = Split(sParams, ",")
        For i = 0 To UBound(partes)
            par = Split(partes(i), ":")
            tipoDef = Split(Trim(par(1)), "=")
            Set p = m.Parameters.AddNew(Trim(par(0)), Trim(tipoDef(0)))
            If UBound(tipoDef) > 0 Then p.Default = Trim(tipoDef(1))
            p.Kind = "in"
            p.Position = i
            p.Update
        Next
        m.Parameters.Refresh
    End If

    m.Update
    oClase.Methods.Refresh
End Sub

'------------------------------------------------------------------
' Asociacion dirigida (flecha del origen al destino)
'------------------------------------------------------------------
Sub AddAsoc(oOrigen, oDestino, nColor, nAncho)
    Dim con
    Set con = oOrigen.Connectors.AddNew("", "Association")
    con.SupplierID = oDestino.ElementID
    con.Direction = "Source -> Destination"
    con.Color = nColor
    con.Width = nAncho
    con.Update
    oOrigen.Connectors.Refresh
End Sub

'------------------------------------------------------------------
' Herencia: la subclase extiende la clase abstracta (triangulo vacio)
'------------------------------------------------------------------
Sub AddHereda(oHija, oPadre, nColor)
    Dim con
    Set con = oHija.Connectors.AddNew("", "Generalization")
    con.SupplierID = oPadre.ElementID
    con.Color = nColor
    con.Width = 2
    con.Update
    oHija.Connectors.Refresh
End Sub

'------------------------------------------------------------------
' Coloca un elemento en el diagrama con su color de fondo y borde
'------------------------------------------------------------------
Sub AddObj(oElem, l, r, t, b, nSeq, nFondo, nBorde)
    Dim dobj
    Set dobj = oDiagram.DiagramObjects.AddNew("l=" & l & ";r=" & r & ";t=" & t & ";b=" & b & ";", "")
    dobj.ElementID = oElem.ElementID
    dobj.Sequence = nSeq
    dobj.BackgroundColor = nFondo
    dobj.BorderColor = nBorde
    dobj.Update
End Sub

Main
