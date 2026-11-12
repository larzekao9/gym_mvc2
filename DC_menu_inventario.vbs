'==================================================================
' Script de Enterprise Architect (VBScript)
' DIAGRAMA DE CLASES - Patron Strategy: Servicios Adicionales (gym mvc)
'
' Refleja el codigo tal cual: cada atributo y metodo con su visibilidad
' real (+ public / - private), static, tipos y valores por defecto.
'
'   ServicioView ---------------> ServicioController
'   ServicioController ---------> ServicioModel / ContratacionServicioModel / UsuarioModel
'   ServicioController ---------> ContratacionServicioContext
'   ContratacionServicioContext <>---> CategoriaSocioStrategy   (agregacion)
'   SocioNormal/VIP/Estudiante/Corporativo/Promocion - - -|> CategoriaSocioStrategy (realizacion)
'   Modelos --------------------> Conexion
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
Const NARANJA       = 11917818   ' #FAD9B5  vista, modelos, conexion
Const BORDE_NARANJA = 8945678    ' #0E8088
Const AZUL          = 16574682   ' #DAE8FC  controlador, context, strategy
Const BORDE_AZUL    = 12553836   ' #6C8EBF
Const ROJO          = 178        ' #B20000  flechas a modelos y conexion
Const AZUL_FLECHA   = 16711680   ' #0000FF  flechas del patron

Dim oDiagram
Dim oPackage

Dim clsView
Dim clsController
Dim clsContext
Dim intStrategy
Dim clsNormal
Dim clsVIP
Dim clsEstudiante
Dim clsCorporativo
Dim clsPromocion
Dim clsServicioModel
Dim clsContratacionModel
Dim clsUsuarioModel
Dim clsConexion

Sub Main

    Set oPackage = Repository.GetTreeSelectedPackage()
    If oPackage Is Nothing Then
        Session.Prompt "Selecciona primero un paquete en el Browser.", 0
        Exit Sub
    End If

    ' ---------- Crear el diagrama de Clases ----------
    Set oDiagram = oPackage.Diagrams.AddNew("DC - Strategy Servicios Adicionales", "Class")
    oDiagram.Update
    oPackage.Diagrams.Refresh

    ' ==============================================================
    ' ServicioView   <- views/ServicioView.php
    ' ==============================================================
    Set clsView = NuevaClase("ServicioView", "Class")
    AddMet clsView, "render", "void", "clientes:array,servicios:array,historial:array,recibo:?array,opciones:array=[]", "Public", False, 0

    ' ==============================================================
    ' ServicioController   <- controllers/ServicioController.php
    ' ==============================================================
    Set clsController = NuevaClase("ServicioController", "Class")
    AddAttr clsController, "strategies", "array", "Public", False, 0
    AddAttr clsController, "usuarioModel", "UsuarioModel", "Public", False, 1
    AddAttr clsController, "servicioModel", "ServicioModel", "Public", False, 2
    AddAttr clsController, "contratacionModel", "ContratacionServicioModel", "Public", False, 3
    AddAttr clsController, "vista", "ServicioView", "Public", False, 4
    AddMet clsController, "__construct", "", "usuarioModel:UsuarioModel,servicioModel:ServicioModel,contratacionModel:ContratacionServicioModel,vista:ServicioView", "Public", False, 0
    AddMet clsController, "handle", "void", "metodo:string,datos:array", "Public", False, 1
    AddMet clsController, "procesarConEstrategia", "array", "datos:array,preview:bool", "Private", False, 2

    ' ==============================================================
    ' ContratacionServicioContext   <- context/ContratacionServicioContext.php
    ' ==============================================================
    Set clsContext = NuevaClase("ContratacionServicioContext", "Class")
    AddAttr clsContext, "strategy", "CategoriaSocioStrategy", "Private", False, 0
    AddAttr clsContext, "usuario", "array", "Public", False, 1
    AddAttr clsContext, "servicio", "array", "Public", False, 2
    AddAttr clsContext, "precioBase", "float", "Public", False, 3
    AddAttr clsContext, "descuento", "float", "Public", False, 4
    AddAttr clsContext, "precioFinal", "float", "Public", False, 5
    AddMet clsContext, "setStrategy", "void", "strategy:CategoriaSocioStrategy", "Public", False, 0
    AddMet clsContext, "procesarContratacion", "array", "usuario:array,servicio:array", "Public", False, 1

    ' ==============================================================
    ' CategoriaSocioStrategy   <- strategies/CategoriaSocioStrategy.php
    ' ==============================================================
    Set intStrategy = NuevaClase("CategoriaSocioStrategy", "Interface")
    AddMet intStrategy, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    ' ==============================================================
    ' Estrategias concretas   <- strategies/Socio*.php
    ' ==============================================================
    Set clsNormal = NuevaClase("SocioNormal", "Class")
    AddMet clsNormal, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    Set clsVIP = NuevaClase("SocioVIP", "Class")
    AddMet clsVIP, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    Set clsEstudiante = NuevaClase("SocioEstudiante", "Class")
    AddMet clsEstudiante, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    Set clsCorporativo = NuevaClase("SocioCorporativo", "Class")
    AddMet clsCorporativo, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    Set clsPromocion = NuevaClase("SocioPromocion", "Class")
    AddMet clsPromocion, "calcular", "float", "precio:float,servicio:array", "Public", False, 0

    ' ==============================================================
    ' ServicioModel   <- models/ServicioModel.php
    ' ==============================================================
    Set clsServicioModel = NuevaClase("ServicioModel", "Class")
    AddAttr clsServicioModel, "id", "int", "Public", False, 0
    AddAttr clsServicioModel, "nombre", "string", "Public", False, 1
    AddAttr clsServicioModel, "descripcion", "string", "Public", False, 2
    AddAttr clsServicioModel, "precio", "float", "Public", False, 3
    AddAttr clsServicioModel, "conexion", "PDO", "Private", False, 4
    AddMet clsServicioModel, "__construct", "", "", "Public", False, 0
    AddMet clsServicioModel, "obtenerTodos", "array", "", "Public", False, 1
    AddMet clsServicioModel, "buscarPorId", "?array", "id:int", "Public", False, 2

    ' ==============================================================
    ' ContratacionServicioModel   <- models/ContratacionServicioModel.php
    ' ==============================================================
    Set clsContratacionModel = NuevaClase("ContratacionServicioModel", "Class")
    AddAttr clsContratacionModel, "id", "int", "Public", False, 0
    AddAttr clsContratacionModel, "usuarioId", "int", "Public", False, 1
    AddAttr clsContratacionModel, "servicioId", "int", "Public", False, 2
    AddAttr clsContratacionModel, "categoriaSocio", "string", "Public", False, 3
    AddAttr clsContratacionModel, "precioBase", "float", "Public", False, 4
    AddAttr clsContratacionModel, "descuento", "float", "Public", False, 5
    AddAttr clsContratacionModel, "precioFinal", "float", "Public", False, 6
    AddAttr clsContratacionModel, "createdAt", "string", "Public", False, 7
    AddAttr clsContratacionModel, "conexion", "PDO", "Private", False, 8
    AddMet clsContratacionModel, "__construct", "", "", "Public", False, 0
    AddMet clsContratacionModel, "registrar", "int", "d:array", "Public", False, 1
    AddMet clsContratacionModel, "obtenerHistorial", "array", "limit:int=20", "Public", False, 2

    ' ==============================================================
    ' UsuarioModel   <- models/UsuarioModel.php
    ' ==============================================================
    Set clsUsuarioModel = NuevaClase("UsuarioModel", "Class")
    AddAttr clsUsuarioModel, "id", "int", "Public", False, 0
    AddAttr clsUsuarioModel, "nombre", "string", "Public", False, 1
    AddAttr clsUsuarioModel, "apellido", "string", "Public", False, 2
    AddAttr clsUsuarioModel, "email", "string", "Public", False, 3
    AddAttr clsUsuarioModel, "password", "string", "Public", False, 4
    AddAttr clsUsuarioModel, "rol", "string", "Public", False, 5
    AddAttr clsUsuarioModel, "conexion", "PDO", "Private", False, 6
    AddMet clsUsuarioModel, "__construct", "", "", "Public", False, 0
    AddMet clsUsuarioModel, "crear", "void", "datos:array", "Public", False, 1
    AddMet clsUsuarioModel, "autenticar", "?array", "email:string,password:string", "Public", False, 2
    AddMet clsUsuarioModel, "obtenerTodos", "array", "", "Public", False, 3
    AddMet clsUsuarioModel, "obtenerPorId", "?array", "id:int", "Public", False, 4
    AddMet clsUsuarioModel, "actualizar", "void", "id:int,datos:array", "Public", False, 5
    AddMet clsUsuarioModel, "eliminar", "void", "id:int", "Public", False, 6

    ' ==============================================================
    ' Conexion   <- Conexion.php  (singleton)
    ' ==============================================================
    Set clsConexion = NuevaClase("Conexion", "Class")
    AddAttr clsConexion, "instancia", "?PDO", "Private", True, 0
    AddMet clsConexion, "getConexion", "PDO", "", "Public", True, 0

    ' ---------- Relaciones ----------
    ' AddAsoc origen, destino, color, ancho   (flecha hacia el destino)
    AddAsoc clsView, clsController, ROJO, 1
    AddAsoc clsController, clsServicioModel, ROJO, 1
    AddAsoc clsController, clsContratacionModel, ROJO, 1
    AddAsoc clsController, clsUsuarioModel, ROJO, 1
    AddAsoc clsController, clsContext, AZUL_FLECHA, 2
    AddAsoc clsServicioModel, clsConexion, ROJO, 1
    AddAsoc clsContratacionModel, clsConexion, ROJO, 1
    AddAsoc clsUsuarioModel, clsConexion, ROJO, 1
    ' AddAgreg parte, todo   (rombo vacio en el todo)
    AddAgreg intStrategy, clsContext, AZUL_FLECHA, 2
    ' AddRealiza concreta, interfaz   (linea punteada con triangulo vacio)
    AddRealiza clsNormal, intStrategy, AZUL_FLECHA
    AddRealiza clsVIP, intStrategy, AZUL_FLECHA
    AddRealiza clsEstudiante, intStrategy, AZUL_FLECHA
    AddRealiza clsCorporativo, intStrategy, AZUL_FLECHA
    AddRealiza clsPromocion, intStrategy, AZUL_FLECHA

    ' ---------- Colocar en el diagrama (t y b negativos en EA) ----------
    AddObj clsView, 20, 340, -340, -440, 1, NARANJA, BORDE_NARANJA
    AddObj clsController, 400, 790, -300, -520, 2, AZUL, BORDE_AZUL
    AddObj clsContext, 850, 1180, -300, -500, 3, AZUL, BORDE_AZUL
    AddObj intStrategy, 850, 1180, -620, -700, 4, AZUL, BORDE_AZUL
    AddObj clsNormal, 400, 620, -840, -910, 5, AZUL, BORDE_AZUL
    AddObj clsVIP, 640, 860, -840, -910, 6, AZUL, BORDE_AZUL
    AddObj clsEstudiante, 880, 1100, -840, -910, 7, AZUL, BORDE_AZUL
    AddObj clsCorporativo, 1120, 1340, -840, -910, 8, AZUL, BORDE_AZUL
    AddObj clsPromocion, 1360, 1580, -840, -910, 9, AZUL, BORDE_AZUL
    AddObj clsServicioModel, 1260, 1560, -20, -200, 10, NARANJA, BORDE_NARANJA
    AddObj clsUsuarioModel, 1260, 1560, -300, -590, 11, NARANJA, BORDE_NARANJA
    AddObj clsContratacionModel, 1640, 1960, -160, -420, 12, NARANJA, BORDE_NARANJA
    AddObj clsConexion, 2040, 2300, -200, -280, 13, NARANJA, BORDE_NARANJA
    oDiagram.DiagramObjects.Refresh

    Repository.ReloadDiagram oDiagram.DiagramID
    Repository.OpenDiagram oDiagram.DiagramID

    Session.Prompt "Diagrama 'Strategy Servicios Adicionales' creado (12 clases + 1 interfaz).", 0

End Sub

'------------------------------------------------------------------
' Crea una clase (o interfaz) en el paquete
'------------------------------------------------------------------
Function NuevaClase(sNombre, sTipo)
    Dim el
    Set el = oPackage.Elements.AddNew(sNombre, sTipo)
    el.Update
    oPackage.Elements.Refresh
    Set NuevaClase = el
End Function

'------------------------------------------------------------------
' Atributo con su visibilidad real ("Public" / "Private") y static
'------------------------------------------------------------------
Sub AddAttr(oClase, sNombre, sTipo, sVisibilidad, bStatic, nPos)
    Dim a
    Set a = oClase.Attributes.AddNew(sNombre, sTipo)
    a.Visibility = sVisibilidad
    a.IsStatic = bStatic
    a.Pos = nPos
    a.Update
    oClase.Attributes.Refresh
End Sub

'------------------------------------------------------------------
' Metodo con parametros "nombre:tipo,nombre:tipo=valorPorDefecto"
'   El metodo se guarda ANTES de agregarle parametros.
'------------------------------------------------------------------
Sub AddMet(oClase, sNombre, sRetorno, sParams, sVisibilidad, bStatic, nPos)
    Dim m, p, partes, i, par, tipoDef
    Set m = oClase.Methods.AddNew(sNombre, sRetorno)
    m.Visibility = sVisibilidad
    m.IsStatic = bStatic
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
' Agregacion: el Context TIENE una Strategy (rombo vacio en el todo)
'------------------------------------------------------------------
Sub AddAgreg(oParte, oTodo, nColor, nAncho)
    Dim con
    Set con = oParte.Connectors.AddNew("", "Aggregation")
    con.SupplierID = oTodo.ElementID
    con.SupplierEnd.Aggregation = 1
    con.Color = nColor
    con.Width = nAncho
    con.Update
    oParte.Connectors.Refresh
End Sub

'------------------------------------------------------------------
' Realizacion: la clase concreta implementa la interfaz
'------------------------------------------------------------------
Sub AddRealiza(oConcreta, oInterfaz, nColor)
    Dim con
    Set con = oConcreta.Connectors.AddNew("", "Realisation")
    con.SupplierID = oInterfaz.ElementID
    con.Color = nColor
    con.Update
    oConcreta.Connectors.Refresh
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
